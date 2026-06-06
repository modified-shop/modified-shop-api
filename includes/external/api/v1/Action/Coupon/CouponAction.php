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
  final class CouponAction extends BaseAction
  {
      use CouponGetAction;
      use CouponDeleteAction;

      /**
       * Insert a coupon by the given options.
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The coupon data
       */
      public function InsertCoupon(array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if (isset($this->options[TABLE_COUPONS])) {
              $coupon = $this->InsertUpdateCoupon(0, $this->options[TABLE_COUPONS]);
              $couponId = $coupon['coupon_id'];
          }
          
          if (!isset($couponId)) {
              throw new Exception('Coupon ID required');
          } else {
              if (isset($this->options[TABLE_COUPONS_DESCRIPTION])) {
                  $coupon_description = $this->InsertUpdateDescription($couponId, $this->options[TABLE_COUPONS_DESCRIPTION]);
              }
          }
          
          return $this->GetCouponDetails($couponId);
      }

      /**
       * Insert a coupon by the given coupon id and options.
       *
       * @param mixed[] $options
       *
       * @return array The coupon data
       */
      public function UpdateCoupon(int $couponId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          $coupon_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_COUPONS."
                                         WHERE coupon_id = '".(int)$couponId."'");
          if (xtc_db_num_rows($coupon_query) < 1) {
              return $this->errormessage(sprintf('Coupon not found: %s', $couponId));
          } else {
              if (isset($this->options[TABLE_COUPONS])) {
                  $coupon = $this->InsertUpdateCoupon($couponId, $this->options[TABLE_COUPONS]);
              }
          }
          
          $coupon_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_COUPONS_DESCRIPTION."
                                         WHERE coupon_id = '".(int)$couponId."'");
          if (xtc_db_num_rows($coupon_query) < 1) {
              return $this->errormessage(sprintf('Coupon description not found: %s', $couponId));
          } else {
              if (isset($this->options[TABLE_COUPONS_DESCRIPTION])) {
                  $coupon_description = $this->InsertUpdateDescription($couponId, $this->options[TABLE_COUPONS_DESCRIPTION]);
              }
          }
          
          return $this->GetCouponDetails($couponId);
      }

      /**
       * Insert or Update a coupon by the given coupon id.
       *
       * @param int $couponId The coupon id
       * @param mixed[] $options
       *
       * @return array The coupon data
       */
      public function InsertUpdateCoupon(int $couponId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($couponId > 0) {
              $action = 'update';
              $coupon_query = xtc_db_query("SELECT *
                                              FROM ".TABLE_COUPONS."
                                             WHERE coupon_id = '".(int)$couponId."'");
              if (xtc_db_num_rows($coupon_query) < 1) {
                  return $this->errormessage(sprintf('Coupon not found: %s', $couponId));
              } else {
                  $coupon = xtc_db_fetch_array($coupon_query);
                  $coupon['date_modified'] = 'now()';
              }
          } else {
              $action = 'insert';
              $coupon = $this->getDefaultTableValues(TABLE_COUPONS);
              $coupon['date_created'] = 'now()';
          }
          
          foreach ($coupon as $key => $value) {
              if (isset($this->options[$key])) {
                  $coupon[$key] = $this->options[$key];
              }
          }
          
          // Input validation
          $this->checkTableData(TABLE_COUPONS, $coupon);
          unset($coupon['coupon_id']);
                    
          xtc_db_perform(TABLE_COUPONS, $coupon, $action, "coupon_id = '".(int)$couponId."'");
          if ($action == 'insert') {
              $couponId = xtc_db_insert_id();
          }          

          return $this->GetCoupon($couponId);
      }

      /**
       * Insert or Update a coupon by the given coupon id.
       *
       * @param int $couponId The coupon id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The coupon data
       */
      public function InsertUpdateDescription(int $couponId, array $options): array
      {
          // Input validation
          if (empty($couponId)) {
              throw new Exception('Coupon ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $coupon_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_COUPONS."
                                         WHERE coupon_id = '".(int)$couponId."'");
          if (xtc_db_num_rows($coupon_query) < 1) {
              return $this->errormessage(sprintf('Coupon not found: %s', $couponId));
          } else {
              $languages_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_LANGUAGES);
              while ($languages = xtc_db_fetch_array($languages_query)) { 
                  $coupon_description_query = xtc_db_query("SELECT *
                                                              FROM ".TABLE_COUPONS_DESCRIPTION."
                                                             WHERE coupon_id = '".(int)$couponId."'
                                                               AND language_id = '".(int)$languages['languages_id']."'");
                  if (xtc_db_num_rows($coupon_description_query) > 0) {
                      $coupon_description = xtc_db_fetch_array($coupon_description_query);

                      foreach ($coupon_description as $key => $value) {
                          if (isset($this->options[$languages['code']][$key])) {
                              $coupon_description[$key] = $this->options[$languages['code']][$key];
                          }
                      }

                      // Input validation
                      $this->checkTableData(TABLE_COUPONS_DESCRIPTION, $coupon_description);
                      xtc_db_perform(TABLE_COUPONS_DESCRIPTION, $coupon_description, 'update', "coupon_id = '".(int)$couponId."' AND language_id = '".(int)$languages['languages_id']."'");
                  } elseif (isset($this->options[$languages['code']])) {
                      $coupon_description = $this->getDefaultTableValues(TABLE_COUPONS_DESCRIPTION);
                      $coupon_description['coupon_id'] = (int)$couponId;
                      $coupon_description['language_id'] = (int)$languages['languages_id'];
                
                      foreach ($coupon_description as $key => $value) {
                          if (isset($this->options[$languages['code']][$key])) {
                              $coupon_description[$key] = $this->options[$languages['code']][$key];
                          }
                      }

                      // Input validation
                      $this->checkTableData(TABLE_COUPONS_DESCRIPTION, $coupon_description);
                      xtc_db_perform(TABLE_COUPONS_DESCRIPTION, $coupon_description);
                  }
              }            
          }

          return $this->GetCouponDescription($couponId);
      }
      
  }
