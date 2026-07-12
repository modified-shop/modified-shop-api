<?php

/**
 * /includes/external/api/v1/Webhook/EventRecorder.php
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

/**
 * Records shop events into the api_events queue table.
 *
 * Called from auto-include hook files in shop frontend/admin context where
 * neither the Slim app nor its autoloader are running — this class must stay
 * dependency-free and every failure path must be a silent no-op so that the
 * shop (especially the checkout) can never be broken by the webhook feature.
 */
final class EventRecorder
{
    /**
     * @var bool|null
     */
    private static $tableExists = null;

    /**
     * Insert one event into the queue; silent no-op on any precondition failure.
     *
     * @param string $eventType The event type (see EventTypes whitelist)
     * @param int $entityId The primary entity id (e.g. orders_id)
     * @param array<mixed> $payload Small id-only payload, JSON-encoded (max 2048 bytes)
     *
     * @return void
     */
    public static function record(string $eventType, int $entityId, array $payload): void
    {
        try {
            if (!defined('MODULE_API_ACCESS_STATUS') || MODULE_API_ACCESS_STATUS != 'true') {
                return;
            }
            if (!defined('MODULE_API_ACCESS_WEBHOOKS_STATUS') || MODULE_API_ACCESS_WEBHOOKS_STATUS != 'true') {
                return;
            }
            if (self::$tableExists === null) {
                $check_query = xtc_db_query("SHOW TABLES LIKE 'api_events'");
                self::$tableExists = xtc_db_num_rows($check_query) > 0;
            }
            if (self::$tableExists !== true) {
                return;
            }

            $json = json_encode($payload);
            if (!is_string($json) || strlen($json) > 2048) {
                $json = '{}';
            }

            $sql_data_array = array(
                'event_type' => substr($eventType, 0, 64),
                'entity_id' => $entityId,
                'payload' => $json,
                'created_at' => time(),
            );
            xtc_db_perform('api_events', $sql_data_array);
        } catch (\Throwable $t) {
            // never disturb the shop
        }
    }
}
