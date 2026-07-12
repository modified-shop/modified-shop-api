<?php

/**
 * /includes/external/api/v1/config/routes.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Tuupola\Middleware\JwtAuthentication;

return function (App $app) {
    $settings = $app->getContainer()->get('settings');

    // oauth
    include __DIR__ . '/oauth.php';

    // meta
    include __DIR__ . '/meta.php';

    // events
    include __DIR__ . '/events.php';

    // routes
    $app->group(
        '/v1',
        function (RouteCollectorProxy $app) {
            // me
            include __DIR__ . '/me.php';

            // customers
            include __DIR__ . '/customers.php';

            // categories
            include __DIR__ . '/categories.php';

            // products
            include __DIR__ . '/products.php';

            // manufacturers
            include __DIR__ . '/manufacturers.php';

            // attributes
            include __DIR__ . '/attributes.php';

            // tags
            include __DIR__ . '/tags.php';

            // orders
            include __DIR__ . '/orders.php';

            // countries
            include __DIR__ . '/countries.php';

            // shipping
            include __DIR__ . '/shipping.php';

            // contents
            include __DIR__ . '/contents.php';

            // campaigns
            include __DIR__ . '/campaigns.php';

            // currencies
            include __DIR__ . '/currencies.php';

            // languages
            include __DIR__ . '/languages.php';

            // newsletters
            include __DIR__ . '/newsletters.php';

            // configurations
            include __DIR__ . '/configurations.php';

            // coupons
            include __DIR__ . '/coupons.php';

            // dhl
            include __DIR__ . '/dhl.php';

            // schema
            include __DIR__ . '/schema.php';

            // webhooks
            include __DIR__ . '/webhooks.php';
        }
    )->add(JwtAuthentication::class);
};
