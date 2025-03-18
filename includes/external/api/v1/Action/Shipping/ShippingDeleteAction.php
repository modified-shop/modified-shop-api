<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Shipping;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  trait ShippingDeleteAction
  {
      /**
       * Delete a carrier by the given carrier id.
       *
       * @param int $carrierId The carrier id
       *
       * @return void
       */
      public function DeleteCarrier(int $carrierId): void
      {
          // Input validation
          if (empty($carrierId)) {
              throw new Exception('Carrier ID required');
          }

          $carrier_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_CARRIERS."
                                          WHERE carrier_id = '".(int)$carrierId."'");
          if (xtc_db_num_rows($carrier_query) < 1) {
            throw new Exception(sprintf('Carrier not found: %s', $carrierId));
          } else {
              //delete
              xtc_db_query("DELETE FROM ".TABLE_CARRIERS." WHERE carrier_id = '".(int)$carrierId."'");
          }
          
          $this->logger->info(sprintf('Carrier deleted successfully: %s', $carrierId));
      }

      /**
       * Delete a shipping status by the given shipping status id.
       *
       * @param int $shippingStatusId The shipping status id
       *
       * @return void
       */
      public function DeleteShippingStatus(int $shippingStatusId): void
      {
          // Input validation
          if (empty($shippingStatusId)) {
              throw new Exception('Shipping Status ID required');
          }

          $shipping_status_query = xtc_db_query("SELECT *
                                                   FROM ".TABLE_SHIPPING_STATUS."
                                                  WHERE shipping_status_id = '".(int)$shippingStatusId."'");
          if (xtc_db_num_rows($shipping_status_query) < 1) {
            throw new Exception(sprintf('Shipping Status not found: %s', $shippingStatusId));
          } else {
              //delete
              xtc_db_query("DELETE FROM ".TABLE_SHIPPING_STATUS." WHERE shipping_status_id = '".(int)$shippingStatusId."'");
          }
          
          $this->logger->info(sprintf('Shipping Status deleted successfully: %s', $shippingStatusId));
      }

  }
