<?php

/**
 * /includes/external/api/v1/config/coupons.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// coupons
$app->get('/coupons', \api\v1\Service\Coupon\GetCoupons::class);
$app->get('/coupons/{id}', \api\v1\Service\Coupon\GetSingleCoupon::class);
$app->get('/coupons/{id}/coupons', \api\v1\Service\Coupon\GetCoupon::class);
$app->get('/coupons/{id}/description', \api\v1\Service\Coupon\GetCouponDescription::class);

// insert coupons
$app->post('/coupons', \api\v1\Service\Coupon\InsertCoupon::class);
$app->post('/coupons/{id}/coupons', \api\v1\Service\Coupon\InsertUpdateCoupon::class);
$app->post('/coupons/{id}/description', \api\v1\Service\Coupon\InsertUpdateDescription::class);

// update coupons
$app->put('/coupons/{id}', \api\v1\Service\Coupon\UpdateCoupon::class);
$app->put('/coupons/{id}/coupons', \api\v1\Service\Coupon\InsertUpdateCoupon::class);
$app->put('/coupons/{id}/description', \api\v1\Service\Coupon\InsertUpdateDescription::class);

// delete coupons
$app->delete('/coupons/{id}', \api\v1\Service\Coupon\DeleteCoupon::class);
