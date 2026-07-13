<?php

/**
 * /includes/extra/checkout/checkout_process_end/api_webhooks.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// Records an order.created event for the API webhook queue. Runs at the end
// of checkout_process where $insert_id holds the new order id. Every guard
// below is deliberate: this file must never be able to break the checkout.
if (
    defined('MODULE_API_ACCESS_STATUS') && MODULE_API_ACCESS_STATUS == 'true'
    && defined('MODULE_API_ACCESS_WEBHOOKS_STATUS') && MODULE_API_ACCESS_WEBHOOKS_STATUS == 'true'
) {
    try {
        if (
            isset($insert_id)
            && (int)$insert_id > 0
            && file_exists(DIR_FS_EXTERNAL . 'api/v1/Webhook/EventRecorder.php')
        ) {
            require_once(DIR_FS_EXTERNAL . 'api/v1/Webhook/EventRecorder.php');

            \api\v1\Webhook\EventRecorder::record(
                'order.created',
                (int)$insert_id,
                array('orders_id' => (int)$insert_id)
            );
        }
    } catch (\Throwable $t) {
        // never break the checkout
    }
}
