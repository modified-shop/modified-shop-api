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

  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  trait AttributesDeleteAction
  {
      /**
       * Delete a value from options by the given option id and value id.
       *
       * @param int $optionId The option id
       * @param int $valueId The value id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAttributes(int $optionId, int $valueId): void
      {
          // Input validation
          if (empty($optionId)) {
              throw new Exception('Product ID required');
          }

          $where = '';
          if ($valueId > 0) {
              $where = "AND products_options_values_id = '".(int)$valueId."'";
          }

          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS."
                                          WHERE products_options_id = '".(int)$optionId."'
                                                ".$where);
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Option value not found: %s', $optionId));
          } else {
              while ($product = xtc_db_fetch_array($product_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." 
                                      WHERE products_options_id = '".(int)$optionId."'
                                        AND products_options_values_id = '".(int)$product['products_options_values_id']."'");
              }
          }
      }

      /**
       * Delete all values from options by the given option id.
       *
       * @param int $optionId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllAttributes(int $optionId): void
      {
          // Input validation
          if (empty($optionId)) {
              throw new Exception('Option ID required');
          }

          $this->DeleteAttributes($optionId, 0);
      }

  }
