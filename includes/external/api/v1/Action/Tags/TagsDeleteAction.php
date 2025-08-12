<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Tags;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  trait TagsDeleteAction
  {
      /**
       * Delete a option by the given option id.
       *
       * @param int $optionId The option id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteOption(int $optionId): void
      {
          // Input validation
          if (empty($optionId)) {
              throw new Exception('Option ID required');
          }

          $option_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_PRODUCTS_TAGS_OPTIONS."
                                         WHERE options_id = '".(int)$optionId."'");
          if (xtc_db_num_rows($option_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Option not found: %s', $optionId));
          } else {
              $products_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_PRODUCTS_TAGS_VALUES." 
                                               WHERE options_id = '".(int)$optionId."'");
              $count = xtc_db_num_rows($products_query);
              if ($count > 0) {
                  $this->errormessage(sprintf('Option can not get deleted due to connected values: %s', $count), 400);
              } else {
                  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_TAGS_OPTIONS." 
                                      WHERE options_id = '".(int)$optionId."'");
              }
          }
      }

      /**
       * Delete a value by the given value id.
       *
       * @param int $valueId The value id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteValue(int $valueId): void
      {
          // Input validation
          if (empty($valueId)) {
              throw new Exception('Value ID required');
          }

          $value_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_PRODUCTS_TAGS_VALUES."
                                        WHERE values_id = '".(int)$valueId."'");
          if (xtc_db_num_rows($value_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Value not found: %s', $valueId));
          } else {
              xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_TAGS_VALUES." 
                                  WHERE values_id = '".(int)$valueId."'");
          }
      }

  }
