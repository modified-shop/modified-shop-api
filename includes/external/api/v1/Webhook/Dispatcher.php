<?php

/**
 * /includes/external/api/v1/Webhook/Dispatcher.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Webhook;

use api\v1\Utility\LoggerHandler;
use Psr\Log\LoggerInterface;

/**
 * Processes the webhook queue: fans out recorded events to matching
 * subscriptions and delivers signed POST requests with retry/backoff.
 * Triggered periodically via DispatchEndpoint (hosting cron) or bin/dispatch.php.
 */
final class Dispatcher
{
    /**
     * Wall-clock budget per run; checked between deliveries so a slow
     * receiver cannot exceed it by more than one request timeout.
     */
    private const MAX_RUNTIME_SECONDS = 20;
    private const MAX_FANOUT_EVENTS = 200;
    private const MAX_DELIVERIES_PER_RUN = 50;
    private const MAX_ATTEMPTS = 6;

    /**
     * Backoff after failed attempt n = RETRY_DELAYS[n - 1] seconds.
     *
     * @var int[]
     */
    private const RETRY_DELAYS = [120, 600, 3600, 21600, 86400];
    private const AUTO_DISABLE_AFTER = 10;
    private const CONNECT_TIMEOUT_SECONDS = 5;
    private const REQUEST_TIMEOUT_SECONDS = 10;

    /**
     * Cleanup runs on average every Nth dispatch (see TokenIssuer::PURGE_ODDS).
     */
    private const PURGE_ODDS = 10;
    private const RETENTION_SECONDS = 2592000;           // 30 days
    private const UNMATCHED_RETENTION_SECONDS = 604800;  // 7 days
    private const LOCK_NAME = 'api_webhooks_dispatch';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array<string, int>
     */
    private $stats = [];

    /**
     * @var array<int, array>
     */
    private $accessRowCache = [];

    /**
     * The constructor.
     *
     * @param LoggerHandler $LoggerHandler The logger factory
     */
    public function __construct(LoggerHandler $LoggerHandler)
    {
        $this->logger = $LoggerHandler->createLogger();
    }

    /**
     * Run one bounded dispatch cycle.
     *
     * @return array The run statistics
     */
    public function run(): array
    {
        $this->stats = [
            'fanned_out' => 0,
            'delivered' => 0,
            'failed' => 0,
            'retried' => 0,
            'skipped' => 0,
            'purged' => 0,
        ];

        if (!$this->acquireLock()) {
            return ['locked' => true];
        }

        /* Worst case is MAX_RUNTIME_SECONDS plus one full request timeout; */
        /* make sure a tight hosting max_execution_time cannot kill the run */
        /* mid-delivery (the lock would auto-release, but the attempt would */
        /* not be recorded). Suppressed: some hosts disable set_time_limit. */
        @set_time_limit(self::MAX_RUNTIME_SECONDS + (2 * self::REQUEST_TIMEOUT_SECONDS) + 30);

        $started = microtime(true);
        try {
            $this->fanOut();
            $this->deliverDue($started);

            if (random_int(1, self::PURGE_ODDS) === 1) {
                $this->purge();
            }
        } finally {
            $this->releaseLock();
        }

        $this->stats['runtime_ms'] = (int)round((microtime(true) - $started) * 1000);

        return $this->stats;
    }

    /**
     * Create pending delivery rows for new events x matching subscriptions.
     *
     * @return void
     */
    private function fanOut(): void
    {
        $events = [];
        $events_query = xtc_db_query("SELECT event_id, event_type, entity_id
                                          FROM `api_events`
                                         WHERE fanned_out = 0
                                         ORDER BY event_id
                                         LIMIT " . self::MAX_FANOUT_EVENTS);
        while ($row = xtc_db_fetch_array($events_query)) {
            $events[] = $row;
        }
        if (count($events) < 1) {
            return;
        }

        /* This dispatcher delivers webhooks only; the apns transport (stage 2) */
        /* gets its own sender and must not receive webhook POSTs. */
        $subscriptions = [];
        $subscriptions_query = xtc_db_query("SELECT subscription_id, customers_id, event_types
                                                 FROM `api_subscriptions`
                                                WHERE active = 1
                                                  AND transport = 'webhook'");
        while ($row = xtc_db_fetch_array($subscriptions_query)) {
            $eventTypes = json_decode((string)$row['event_types'], true);
            $row['event_types'] = is_array($eventTypes) ? $eventTypes : [];
            $subscriptions[] = $row;
        }

        $now = time();
        foreach ($events as $event) {
            $eventId = (int)$event['event_id'];
            $eventType = (string)$event['event_type'];
            $column = EventTypes::requiredColumn($eventType);

            foreach ($subscriptions as $subscription) {
                if (!in_array($eventType, $subscription['event_types'], true)) {
                    continue;
                }

                /* Re-check the owner's permission at fan-out time (not only at */
                /* subscription time), so a revoked permission stops deliveries */
                /* immediately; the skipped row keeps the decision auditable. */
                $status = $this->hasPermission((int)$subscription['customers_id'], $column) ? 'pending' : 'skipped';

                xtc_db_query("INSERT IGNORE INTO `api_event_deliveries`
                                          (event_id, subscription_id, delivery_uid, status, next_attempt_at, created_at)
                                   VALUES ('" . $eventId . "',
                                           '" . (int)$subscription['subscription_id'] . "',
                                           '" . bin2hex(random_bytes(16)) . "',
                                           '" . $status . "',
                                           '" . ($status === 'pending' ? $now : 0) . "',
                                           '" . $now . "')");

                if ($status === 'skipped') {
                    $this->stats['skipped']++;
                }
            }

            xtc_db_query("UPDATE `api_events`
                                 SET fanned_out = 1
                               WHERE event_id = '" . $eventId . "'");
            $this->stats['fanned_out']++;
        }
    }

    /**
     * Send all due pending deliveries within the run budgets.
     *
     * @param float $started The run start (microtime)
     *
     * @return void
     */
    private function deliverDue(float $started): void
    {
        $now = time();
        $deliveries = [];
        $due_query = xtc_db_query("SELECT d.delivery_id,
                                           d.subscription_id,
                                           d.delivery_uid,
                                           d.attempts,
                                           e.event_id,
                                           e.event_type,
                                           e.entity_id,
                                           e.payload,
                                           e.created_at AS event_created_at
                                       FROM `api_event_deliveries` d
                                       JOIN `api_events` e
                                            ON e.event_id = d.event_id
                                      WHERE d.status = 'pending'
                                        AND d.next_attempt_at <= '" . $now . "'
                                      ORDER BY d.next_attempt_at
                                      LIMIT " . self::MAX_DELIVERIES_PER_RUN);
        while ($row = xtc_db_fetch_array($due_query)) {
            $deliveries[] = $row;
        }

        foreach ($deliveries as $delivery) {
            if ((microtime(true) - $started) > self::MAX_RUNTIME_SECONDS) {
                return;
            }

            /* Reload the subscription per delivery: it may have been deleted, */
            /* disabled or auto-disabled since the rows were selected. */
            $subscription_query = xtc_db_query("SELECT subscription_id, url, secret, active, consecutive_failures
                                                    FROM `api_subscriptions`
                                                   WHERE subscription_id = '" . (int)$delivery['subscription_id'] . "'
                                                     AND transport = 'webhook'
                                                   LIMIT 1");
            $subscription = xtc_db_fetch_array($subscription_query);

            if (!is_array($subscription) || (int)$subscription['active'] !== 1) {
                $this->markSkipped((int)$delivery['delivery_id'], 'subscription deleted or inactive');
                continue;
            }

            $this->deliver($delivery, $subscription);
        }
    }

    /**
     * Perform one signed delivery attempt and apply the state transitions.
     *
     * @param array $delivery The delivery row (joined with its event)
     * @param array $subscription The subscription row
     *
     * @return void
     */
    private function deliver(array $delivery, array $subscription): void
    {
        $attempt = (int)$delivery['attempts'] + 1;
        $payload = json_decode((string)$delivery['payload'], true);

        $body = (string)json_encode([
            'id' => (int)$delivery['event_id'],
            'event' => (string)$delivery['event_type'],
            'created' => (int)$delivery['event_created_at'],
            'attempt' => $attempt,
            'data' => is_array($payload) && count($payload) > 0 ? $payload : new \stdClass(),
            'links' => $this->linksFor((string)$delivery['event_type'], (int)$delivery['entity_id']),
        ]);

        $timestamp = time();
        $signature = Signer::sign($timestamp, $body, (string)$subscription['secret']);

        [$httpStatus, $error] = $this->send((string)$subscription['url'], $body, [
            'Content-Type: application/json',
            'User-Agent: modified-shop-api-webhook/1.0',
            'X-Modified-Event: ' . (string)$delivery['event_type'],
            'X-Modified-Delivery: ' . (string)$delivery['delivery_uid'],
            'X-Modified-Timestamp: ' . $timestamp,
            'X-Modified-Signature: sha256=' . $signature,
        ]);

        $now = time();
        $subscriptionId = (int)$subscription['subscription_id'];
        $deliveryId = (int)$delivery['delivery_id'];

        if ($httpStatus >= 200 && $httpStatus < 300) {
            xtc_db_query("UPDATE `api_event_deliveries`
                                 SET status = 'success',
                                     attempts = '" . $attempt . "',
                                     last_http_status = '" . (int)$httpStatus . "',
                                     last_error = '',
                                     updated_at = '" . $now . "'
                               WHERE delivery_id = '" . $deliveryId . "'");
            xtc_db_query("UPDATE `api_subscriptions`
                                 SET consecutive_failures = 0,
                                     last_success_at = '" . $now . "'
                               WHERE subscription_id = '" . $subscriptionId . "'");
            $this->stats['delivered']++;

            return;
        }

        $lastError = xtc_db_input(substr($error !== '' ? $error : ('HTTP ' . $httpStatus), 0, 255));

        if ($attempt >= self::MAX_ATTEMPTS) {
            xtc_db_query("UPDATE `api_event_deliveries`
                                 SET status = 'failed',
                                     attempts = '" . $attempt . "',
                                     last_http_status = '" . (int)$httpStatus . "',
                                     last_error = '" . $lastError . "',
                                     updated_at = '" . $now . "'
                               WHERE delivery_id = '" . $deliveryId . "'");
            xtc_db_query("UPDATE `api_subscriptions`
                                 SET consecutive_failures = consecutive_failures + 1,
                                     last_failure_at = '" . $now . "'
                               WHERE subscription_id = '" . $subscriptionId . "'");
            $this->stats['failed']++;

            if ((int)$subscription['consecutive_failures'] + 1 >= self::AUTO_DISABLE_AFTER) {
                xtc_db_query("UPDATE `api_subscriptions`
                                     SET active = 0,
                                         disabled_reason = 'auto-disabled after " . self::AUTO_DISABLE_AFTER . " consecutive failed deliveries',
                                         updated_at = '" . $now . "'
                                   WHERE subscription_id = '" . $subscriptionId . "'");
                $this->logger->warning(sprintf(
                    'webhook subscription %s auto-disabled after %s consecutive failed deliveries',
                    $subscriptionId,
                    self::AUTO_DISABLE_AFTER
                ));
            }

            return;
        }

        $delay = self::RETRY_DELAYS[$attempt - 1] ?? self::RETRY_DELAYS[count(self::RETRY_DELAYS) - 1];
        xtc_db_query("UPDATE `api_event_deliveries`
                             SET attempts = '" . $attempt . "',
                                 next_attempt_at = '" . ($now + $delay) . "',
                                 last_http_status = '" . (int)$httpStatus . "',
                                 last_error = '" . $lastError . "',
                                 updated_at = '" . $now . "'
                           WHERE delivery_id = '" . $deliveryId . "'");
        $this->stats['retried']++;
    }

    /**
     * POST a signed body to the subscription URL.
     *
     * @param string $url The target URL
     * @param string $body The raw JSON body
     * @param string[] $headers The request headers
     *
     * @return array{0: int, 1: string} HTTP status (0 on transport error) and error text
     */
    private function send(string $url, string $body, array $headers): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT_SECONDS,
            CURLOPT_TIMEOUT => self::REQUEST_TIMEOUT_SECONDS,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ]);

        $responseBody = curl_exec($curl);
        $httpStatus = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $curlError = (string)curl_error($curl);

        if ($responseBody === false || $httpStatus === 0) {
            return [0, $curlError !== '' ? $curlError : 'connection failed'];
        }
        if ($httpStatus >= 200 && $httpStatus < 300) {
            return [$httpStatus, ''];
        }

        return [$httpStatus, 'HTTP ' . $httpStatus . ': ' . substr((string)$responseBody, 0, 200)];
    }

    /**
     * REST links a receiver can follow to fetch the full entity.
     *
     * @param string $eventType The event type
     * @param int $entityId The primary entity id
     *
     * @return array<string, string>|\stdClass
     */
    private function linksFor(string $eventType, int $entityId)
    {
        switch ($eventType) {
            case 'order.created':
            case 'order.status_changed':
                return ['order' => '/api/v1/orders/' . $entityId];
        }

        return new \stdClass();
    }

    /**
     * Mark a delivery as skipped.
     *
     * @param int $deliveryId The delivery
     * @param string $reason The reason
     *
     * @return void
     */
    private function markSkipped(int $deliveryId, string $reason): void
    {
        xtc_db_query("UPDATE `api_event_deliveries`
                             SET status = 'skipped',
                                 last_error = '" . xtc_db_input(substr($reason, 0, 255)) . "',
                                 updated_at = '" . time() . "'
                           WHERE delivery_id = '" . $deliveryId . "'");
        $this->stats['skipped']++;
    }

    /**
     * Check whether an account holds an api_access permission column.
     *
     * @param int $customersId The account
     * @param string $column The permission column
     *
     * @return bool
     */
    private function hasPermission(int $customersId, string $column): bool
    {
        if ($column === '') {
            return false;
        }

        if (!isset($this->accessRowCache[$customersId])) {
            $query = xtc_db_query("SELECT *
                                       FROM `api_access`
                                      WHERE customers_id = '" . (int)$customersId . "'
                                      LIMIT 1");
            $row = xtc_db_fetch_array($query);
            $this->accessRowCache[$customersId] = is_array($row) ? $row : [];
        }

        $access = $this->accessRowCache[$customersId];

        return isset($access[$column]) && (int)$access[$column] === 1;
    }

    /**
     * Opportunistic retention cleanup.
     *
     * @return void
     */
    private function purge(): void
    {
        $now = time();
        $purged = 0;

        // terminal deliveries older than the retention window
        xtc_db_query("DELETE FROM `api_event_deliveries`
                            WHERE status IN ('success', 'failed', 'skipped')
                              AND created_at < '" . ($now - self::RETENTION_SECONDS) . "'");
        $purged += (int)xtc_db_affected_rows();

        /* Fanned-out events without any delivery rows: never matched a */
        /* subscription, or all deliveries already fell out of retention. */
        xtc_db_query("DELETE e FROM `api_events` e
                        LEFT JOIN `api_event_deliveries` d
                               ON d.event_id = e.event_id
                            WHERE e.fanned_out = 1
                              AND e.created_at < '" . ($now - self::UNMATCHED_RETENTION_SECONDS) . "'
                              AND d.delivery_id IS NULL");
        $purged += (int)xtc_db_affected_rows();

        $this->stats['purged'] = $purged;
    }

    /**
     * Acquire the cross-process dispatch lock (no waiting).
     *
     * @return bool
     */
    private function acquireLock(): bool
    {
        $query = xtc_db_query("SELECT GET_LOCK('" . self::LOCK_NAME . "', 0) AS locked");
        $row = xtc_db_fetch_array($query);

        return is_array($row) && (int)$row['locked'] === 1;
    }

    /**
     * Release the dispatch lock.
     *
     * @return void
     */
    private function releaseLock(): void
    {
        xtc_db_query("SELECT RELEASE_LOCK('" . self::LOCK_NAME . "')");
    }
}
