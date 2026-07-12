<?php

/**
 * /includes/external/api/v1/config/events.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// webhook dispatch trigger for the hosting cron; authenticated by the static
// cron secret inside the handler, so it lives outside the JWT route group.
// GET and POST are both accepted because some cron UIs can only send GET.
$app->map(['GET', 'POST'], '/v1/events/dispatch', \api\v1\Webhook\DispatchEndpoint::class);
