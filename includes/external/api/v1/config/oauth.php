<?php

/**
 * /includes/external/api/v1/config/oauth.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// oauth
$app->post('/v1/oauth', \api\v1\Auth\JwtAuth::class)
    ->add(\api\v1\Auth\Authentication::class)
    ->add(\api\v1\Auth\RateLimitMiddleware::class);

// oauth token refresh
$app->post('/v1/oauth/refresh', \api\v1\Auth\RefreshToken::class)
    ->add(\api\v1\Auth\RateLimitMiddleware::class);

// oauth logout
$app->post('/v1/oauth/logout', \api\v1\Auth\Logout::class);
