<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action;


  /**
   * Service.
   */
  trait CustomerDeleteAction
  {   
      /**
       * Delete an customer by the given customer id.
       *
       * @param int $customerId The customer id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteCustomer(int $customerId): void
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }

          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
            throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_ADDRESS_BOOK." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_INFO." WHERE customers_info_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_IP." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET_ATTRIBUTES." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_WISHLIST." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_WISHLIST_ATTRIBUTES." WHERE customers_id = '".(int)$customerId."'");
          }
          
          $this->logger->info(sprintf('Customer deleted successfully: %s', $customerId));
      }

  }
