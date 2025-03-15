<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Currency;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  trait CurrencyDeleteAction
  {
      /**
       * Delete a currency by the given currency id.
       *
       * @param int $currencyId The currency id
       *
       * @return void
       */
      public function DeleteCurrency(int $currencyId): void
      {
          // Input validation
          if (empty($currencyId)) {
              throw new Exception('Currency ID required');
          }

          $currency_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CURRENCIES."
                                           WHERE currencies_id = '".(int)$currencyId."'");
          if (xtc_db_num_rows($currency_query) < 1) {
            throw new Exception(sprintf('Currency not found: %s', $currencyId));
          } else {
              //delete
              xtc_db_query("DELETE FROM ".TABLE_CURRENCIES." WHERE currencies_id = '".(int)$currencyId."'");
          }
          
          $this->logger->info(sprintf('Currency deleted successfully: %s', $currencyId));
      }

  }
