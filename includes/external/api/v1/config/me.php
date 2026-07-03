<?php

/**
 * /includes/external/api/v1/config/me.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// me
$app->get('/me', \api\v1\Auth\Me::class);
