<?php

/**
 * /includes/external/api/v1/Webhook/EventTypes.php
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

final class EventTypes
{
    /**
     * Whitelist of event types that are actually fired by shop hooks,
     * mapped to the api_access permission column an account must hold
     * to subscribe to (and receive) the event.
     *
     * Adding a new event = one entry here + one hook file under
     * /includes/extra/... that calls EventRecorder::record().
     *
     * @var array<string, string>
     */
    private const TYPES = [
        'order.created' => 'OrderGetOrders',
    ];

    /**
     * All known event types.
     *
     * @return string[]
     */
    public static function all(): array
    {
        return array_keys(self::TYPES);
    }

    /**
     * Check whether an event type exists.
     *
     * @param string $eventType The event type
     *
     * @return bool
     */
    public static function isValid(string $eventType): bool
    {
        return isset(self::TYPES[$eventType]);
    }

    /**
     * The api_access permission column required to subscribe to an event type.
     *
     * @param string $eventType The event type
     *
     * @return string The column name, or empty string for unknown types
     */
    public static function requiredColumn(string $eventType): string
    {
        return self::TYPES[$eventType] ?? '';
    }
}
