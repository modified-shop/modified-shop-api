<?php

/**
 * /includes/external/api/v1/Action/Coupon/CouponDeleteAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Coupon;

use api\v1\Action\BaseAction;
use api\v1\Utility\LoggerHandler;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Service.
 */
trait CouponDeleteAction
{
    /**
     * Delete a coupon by the given coupon id.
     *
     * @param int $couponId The coupon id
     *
     * @throws Exception
     *
     * @return array<mixed>|null
     */
    public function DeleteCoupon(int $couponId): ?array
    {
        // Input validation
        if (empty($couponId)) {
            throw new Exception('Coupon ID required');
        }

        $coupon_query = xtc_db_query("SELECT *
                                          FROM " . TABLE_COUPONS . "
                                         WHERE coupon_id = '" . (int)$couponId . "'");
        if (xtc_db_num_rows($coupon_query) < 1) {
            return $this->errormessage(sprintf('Coupon not found: %s', $couponId));
        } else {
            xtc_db_query("DELETE FROM " . TABLE_COUPONS . " WHERE coupon_id = '" . (int)$couponId . "'");
            xtc_db_query("DELETE FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = '" . (int)$couponId . "'");

            $this->logger->info(sprintf('Coupon deleted successfully: %s', $couponId));
        }
        return null;
    }
}
