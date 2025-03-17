<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

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
       * @return void
       */
      public function DeleteCoupon(int $couponId): void
      {
          // Input validation
          if (empty($couponId)) {
              throw new Exception('Coupon ID required');
          }

          $coupon_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_COUPONS."
                                         WHERE coupon_id = '".(int)$couponId."'");
          if (xtc_db_num_rows($coupon_query) < 1) {
              throw new Exception(sprintf('Coupon not found: %s', $couponId));
          } else {
              xtc_db_query("DELETE FROM ".TABLE_COUPONS." WHERE coupon_id = '".(int)$couponId."'");
              xtc_db_query("DELETE FROM ".TABLE_COUPONS_DESCRIPTION." WHERE coupon_id = '".(int)$couponId."'");
              
              $this->logger->info(sprintf('Coupon deleted successfully: %s', $couponId));
          }          
      }

  }
