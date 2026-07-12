<?php

/**
 * /includes/external/api/v1/Action/Webhook/WebhookAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Webhook;

use api\v1\Action\BaseAction;
use api\v1\Webhook\EventTypes;
use api\v1\Webhook\UrlValidator;

class WebhookAction extends BaseAction
{
    /**
     * Maximum number of subscriptions per API account (bounds fan-out work).
     */
    private const MAX_SUBSCRIPTIONS = 10;

    /**
     * Maximum number of delivery rows returned for debugging.
     */
    private const MAX_DELIVERIES = 50;

    /**
     * @var array<int, array>
     */
    private $accessRowCache = [];

    /**
     * Resolve the customers_id of the account a JWT belongs to.
     *
     * @param string $email The JWT sub claim (customer email address)
     *
     * @return int The customers_id, or 0 when unknown / without API access
     */
    public function ResolveCustomersId(string $email): int
    {
        if ($email === '') {
            return 0;
        }

        $query = xtc_db_query("SELECT c.customers_id
                                   FROM " . TABLE_CUSTOMERS . " c
                                   JOIN `api_access` aa
                                        ON aa.customers_id = c.customers_id
                                  WHERE c.customers_email_address = '" . xtc_db_input($email) . "'
                                  LIMIT 1");
        $row = xtc_db_fetch_array($query);

        return is_array($row) ? (int)$row['customers_id'] : 0;
    }

    /**
     * Read all subscriptions of an account (never contains the secret).
     *
     * @param int $customersId The owning account
     *
     * @return array The subscriptions
     */
    public function GetSubscriptions(int $customersId): array
    {
        $result = [];
        $query = xtc_db_query("SELECT *
                                   FROM `api_subscriptions`
                                  WHERE customers_id = '" . (int)$customersId . "'
                                    AND transport = 'webhook'
                                  ORDER BY subscription_id");
        while ($row = xtc_db_fetch_array($query)) {
            $result[] = $this->formatSubscription($row);
        }

        return $result;
    }

    /**
     * Read a single subscription of an account.
     *
     * @param int $customersId The owning account
     * @param int $subscriptionId The subscription
     *
     * @return array The subscription data or an errormessage
     */
    public function GetSubscription(int $customersId, int $subscriptionId): array
    {
        $row = $this->loadOwnSubscription($customersId, $subscriptionId);
        if (!is_array($row)) {
            return $this->errormessage(sprintf('Subscription not found: %s', $subscriptionId));
        }

        return $this->formatSubscription($row);
    }

    /**
     * List all known event types plus whether the account may subscribe to them.
     *
     * @param int $customersId The account
     *
     * @return array The event types
     */
    public function GetEventTypes(int $customersId): array
    {
        $result = [];
        foreach (EventTypes::all() as $eventType) {
            $column = EventTypes::requiredColumn($eventType);
            $result[] = [
                'event_type' => $eventType,
                'required_permission' => $column,
                'allowed' => $this->hasPermission($customersId, $column),
            ];
        }

        return $result;
    }

    /**
     * Read the most recent deliveries of a subscription (debugging aid).
     *
     * @param int $customersId The owning account
     * @param int $subscriptionId The subscription
     *
     * @return array The deliveries or an errormessage
     */
    public function GetDeliveries(int $customersId, int $subscriptionId): array
    {
        $subscription = $this->loadOwnSubscription($customersId, $subscriptionId);
        if (!is_array($subscription)) {
            return $this->errormessage(sprintf('Subscription not found: %s', $subscriptionId));
        }

        $result = [];
        $query = xtc_db_query("SELECT d.delivery_uid,
                                       d.status,
                                       d.attempts,
                                       d.next_attempt_at,
                                       d.last_http_status,
                                       d.last_error,
                                       d.created_at,
                                       d.updated_at,
                                       e.event_type,
                                       e.event_id
                                   FROM `api_event_deliveries` d
                                   JOIN `api_events` e
                                        ON e.event_id = d.event_id
                                  WHERE d.subscription_id = '" . (int)$subscriptionId . "'
                                  ORDER BY d.delivery_id DESC
                                  LIMIT " . self::MAX_DELIVERIES);
        while ($row = xtc_db_fetch_array($query)) {
            $result[] = [
                'event_id' => (int)$row['event_id'],
                'event_type' => (string)$row['event_type'],
                'delivery_uid' => (string)$row['delivery_uid'],
                'status' => (string)$row['status'],
                'attempts' => (int)$row['attempts'],
                'next_attempt_at' => (int)$row['next_attempt_at'],
                'last_http_status' => (int)$row['last_http_status'],
                'last_error' => (string)$row['last_error'],
                'created_at' => (int)$row['created_at'],
                'updated_at' => (int)$row['updated_at'],
            ];
        }

        return $result;
    }

    /**
     * Create a subscription. The response is the only place the secret is
     * ever exposed — it cannot be retrieved again later.
     *
     * @param int $customersId The owning account
     * @param mixed[] $data Body data: url, event_types[]
     *
     * @return array The new subscription (including secret) or an errormessage
     */
    public function InsertSubscription(int $customersId, array $data): array
    {
        $url = trim((string)($data['url'] ?? ''));
        $url_error = UrlValidator::validate($url);
        if ($url_error !== '') {
            return $this->errormessage($url_error, 400);
        }

        $types_or_error = $this->validateEventTypes($customersId, $data['event_types'] ?? null);
        if (isset($types_or_error['errormessage'])) {
            return $types_or_error;
        }

        $count_query = xtc_db_query("SELECT COUNT(*) AS cnt
                                         FROM `api_subscriptions`
                                        WHERE customers_id = '" . (int)$customersId . "'");
        $count = xtc_db_fetch_array($count_query);
        if ((int)$count['cnt'] >= self::MAX_SUBSCRIPTIONS) {
            return $this->errormessage(sprintf('Subscription limit reached: %s', self::MAX_SUBSCRIPTIONS), 400);
        }

        $secret = bin2hex(random_bytes(32));
        $sql_data_array = [
            'customers_id' => (int)$customersId,
            'transport' => 'webhook',
            'url' => $url,
            'secret' => $secret,
            'event_types' => (string)json_encode($types_or_error),
            'active' => 1,
            'created_at' => time(),
        ];
        xtc_db_perform('api_subscriptions', $sql_data_array);
        $subscriptionId = xtc_db_insert_id();

        $row = $this->loadOwnSubscription($customersId, (int)$subscriptionId);
        if (!is_array($row)) {
            return $this->errormessage('Subscription could not be created', 500);
        }

        $result = $this->formatSubscription($row);
        $result['secret'] = $secret;

        return $result;
    }

    /**
     * Update url, event_types and/or active of an own subscription.
     * Re-activating resets the failure bookkeeping; the secret is immutable.
     *
     * @param int $customersId The owning account
     * @param int $subscriptionId The subscription
     * @param mixed[] $data Body data: url, event_types[], active
     *
     * @return array The updated subscription or an errormessage
     */
    public function UpdateSubscription(int $customersId, int $subscriptionId, array $data): array
    {
        $row = $this->loadOwnSubscription($customersId, $subscriptionId);
        if (!is_array($row)) {
            return $this->errormessage(sprintf('Subscription not found: %s', $subscriptionId));
        }

        $sql_data_array = [];

        if (array_key_exists('url', $data)) {
            $url = trim((string)$data['url']);
            $url_error = UrlValidator::validate($url);
            if ($url_error !== '') {
                return $this->errormessage($url_error, 400);
            }
            $sql_data_array['url'] = $url;
        }

        if (array_key_exists('event_types', $data)) {
            $types_or_error = $this->validateEventTypes($customersId, $data['event_types']);
            if (isset($types_or_error['errormessage'])) {
                return $types_or_error;
            }
            $sql_data_array['event_types'] = (string)json_encode($types_or_error);
        }

        if (array_key_exists('active', $data)) {
            $active = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $sql_data_array['active'] = $active;
            if ($active === 1 && (int)$row['active'] === 0) {
                $sql_data_array['consecutive_failures'] = 0;
                $sql_data_array['disabled_reason'] = '';
            }
        }

        if (count($sql_data_array) < 1) {
            return $this->errormessage('Nothing to update, allowed fields: url, event_types, active', 400);
        }

        $sql_data_array['updated_at'] = time();
        xtc_db_perform(
            'api_subscriptions',
            $sql_data_array,
            'update',
            "subscription_id = '" . (int)$subscriptionId . "'"
        );

        $row = $this->loadOwnSubscription($customersId, $subscriptionId);

        return $this->formatSubscription((array)$row);
    }

    /**
     * Delete an own subscription including its pending deliveries.
     *
     * @param int $customersId The owning account
     * @param int $subscriptionId The subscription
     *
     * @return array Empty on success or an errormessage
     */
    public function DeleteSubscription(int $customersId, int $subscriptionId): array
    {
        $row = $this->loadOwnSubscription($customersId, $subscriptionId);
        if (!is_array($row)) {
            return $this->errormessage(sprintf('Subscription not found: %s', $subscriptionId));
        }

        xtc_db_query("DELETE FROM `api_event_deliveries`
                            WHERE subscription_id = '" . (int)$subscriptionId . "'
                              AND status = 'pending'");
        xtc_db_query("DELETE FROM `api_subscriptions`
                            WHERE subscription_id = '" . (int)$subscriptionId . "'");

        return [];
    }

    /**
     * Check whether an account holds an api_access permission column.
     *
     * @param int $customersId The account
     * @param string $column The permission column
     *
     * @return bool
     */
    public function hasPermission(int $customersId, string $column): bool
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
     * Validate a request event_types value against whitelist and permissions.
     *
     * @param int $customersId The account
     * @param mixed $eventTypes The raw body value
     *
     * @return array The normalized type list, or an errormessage array
     */
    private function validateEventTypes(int $customersId, $eventTypes): array
    {
        if (!is_array($eventTypes) || count($eventTypes) < 1) {
            return $this->errormessage('event_types must be a non-empty array', 400);
        }

        $normalized = [];
        foreach ($eventTypes as $eventType) {
            if (!is_string($eventType) || !EventTypes::isValid($eventType)) {
                return $this->errormessage(
                    sprintf('Unknown event type: %s', is_scalar($eventType) ? (string)$eventType : gettype($eventType)),
                    400
                );
            }
            $column = EventTypes::requiredColumn($eventType);
            if (!$this->hasPermission($customersId, $column)) {
                return $this->errormessage(
                    sprintf('Access for %s required to subscribe to %s', $column, $eventType),
                    403
                );
            }
            $normalized[$eventType] = $eventType;
        }

        return array_values($normalized);
    }

    /**
     * Load a subscription row only when it belongs to the given account.
     *
     * @param int $customersId The owning account
     * @param int $subscriptionId The subscription
     *
     * @return array|false
     */
    private function loadOwnSubscription(int $customersId, int $subscriptionId)
    {
        if ($subscriptionId < 1) {
            return false;
        }

        /* The /webhooks endpoints manage webhook subscriptions only; apns */
        /* subscriptions (stage 2, /me/devices) are invisible here. */
        $query = xtc_db_query("SELECT *
                                   FROM `api_subscriptions`
                                  WHERE subscription_id = '" . (int)$subscriptionId . "'
                                    AND customers_id = '" . (int)$customersId . "'
                                    AND transport = 'webhook'
                                  LIMIT 1");

        return xtc_db_fetch_array($query);
    }

    /**
     * Map a subscription row to its API representation (without the secret).
     *
     * @param array $row The database row
     *
     * @return array
     */
    private function formatSubscription(array $row): array
    {
        $eventTypes = json_decode((string)($row['event_types'] ?? '[]'), true);

        return [
            'subscription_id' => (int)($row['subscription_id'] ?? 0),
            'url' => (string)($row['url'] ?? ''),
            'event_types' => is_array($eventTypes) ? $eventTypes : [],
            'active' => (int)($row['active'] ?? 0) === 1,
            'consecutive_failures' => (int)($row['consecutive_failures'] ?? 0),
            'disabled_reason' => (string)($row['disabled_reason'] ?? ''),
            'last_success_at' => (int)($row['last_success_at'] ?? 0),
            'last_failure_at' => (int)($row['last_failure_at'] ?? 0),
            'created_at' => (int)($row['created_at'] ?? 0),
            'updated_at' => (int)($row['updated_at'] ?? 0),
        ];
    }
}
