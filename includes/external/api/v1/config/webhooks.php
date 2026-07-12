<?php

/**
 * /includes/external/api/v1/config/webhooks.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// webhooks
$app->get('/webhooks', \api\v1\Service\Webhook\GetSubscriptions::class);
$app->get('/webhooks/event_types', \api\v1\Service\Webhook\GetEventTypes::class);
$app->get('/webhooks/{id}', \api\v1\Service\Webhook\GetSingleSubscription::class);
$app->get('/webhooks/{id}/deliveries', \api\v1\Service\Webhook\GetSubscriptionDeliveries::class);

// insert webhooks
$app->post('/webhooks', \api\v1\Service\Webhook\InsertSubscription::class);

// update webhooks
$app->put('/webhooks/{id}', \api\v1\Service\Webhook\UpdateSubscription::class);

// delete webhooks
$app->delete('/webhooks/{id}', \api\v1\Service\Webhook\DeleteSubscription::class);
