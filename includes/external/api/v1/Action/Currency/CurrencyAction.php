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
  final class CurrencyAction extends BaseAction
  {
      use CurrencyGetAction;
      use CurrencyDeleteAction;
      
      /**
       * Insert an currency by the given options.
       *
       * @param mixed[] $options
       *
       * @return array The currency data
       */
      public function InsertCurrency(array $options): array
      {
          $currency = $this->InsertUpdateCurrency(0, $options);
          
          return $currency;
      }

      /**
       * Insert or Update an currency by the given currency id.
       *
       * @param int $currencyId The currency id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The currency data
       */
      public function InsertUpdateCurrency(int $currencyId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if ($currencyId > 0) {
              $action = 'update';
              $currency_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_CURRENCIES."
                                               WHERE currencies_id = '".(int)$currencyId."'");
              if (xtc_db_num_rows($currency_query) < 1) {
                  throw new Exception(sprintf('Currency not found: %s', $currencyId));
              } else {
                  $currency = xtc_db_fetch_array($currency_query);
                  $currency['last_updated'] = 'now()';
              }
          } else {
              $action = 'insert';
              $currency = $this->getDefaultTableValues(TABLE_CURRENCIES);
          }

          foreach ($currency as $key => $value) {
              if (isset($this->options[$key])) {
                  $currency[$key] = $this->options[$key];
              }
          }

          if ($action == 'insert') {
              $check_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_CURRENCIES."
                                            WHERE code = '".xtc_db_input($currency['code'])."'");
              if (xtc_db_num_rows($check_query) > 0) {
                  throw new Exception('Campaign refId already exists');
              }
          }

          // Input validation
          $this->checkTableData(TABLE_CURRENCIES, $currency);
          unset($currency['currencies_id']);

          xtc_db_perform(TABLE_CURRENCIES, $currency, $action, "currencies_id = '".(int)$currencyId."'");
          if ($action == 'insert') {
              $currencyId = xtc_db_insert_id();
          }

          return $this->GetSingleCurrency($currencyId);
      }

  }
