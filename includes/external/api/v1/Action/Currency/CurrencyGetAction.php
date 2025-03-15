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
  trait CurrencyGetAction
  {
      /**
       * Read a currency by the given currency id.
       *
       * @param int $currencyId The currency id
       *
       * @throws Exception
       *
       * @return array The currency data
       */
      public function getSingleCurrency(int $currencyId): array
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
              $currency = xtc_db_fetch_array($currency_query);
          }
          
          $result = $this->encode_request($currency);
          return $result;
      }

      /**
       * Read campaigns by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The campaigns data
       */
      public function GetCurrencys(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                                                        
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_CURRENCIES);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Currency found');
          }
          
          $data = [];
          $currencies_query = xtc_db_query("SELECT currencies_id
                                              FROM ".TABLE_CURRENCIES."
                                          ORDER BY currencies_id ASC
                                             LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($currencies = xtc_db_fetch_array($currencies_query)) {
              $data[] = $this->getSingleCurrency($currencies['currencies_id']);
          }
          
          $result = [
              'paging' => [
                  'total' => $count['total']
              ],
              'data' => $data
          ];
          
          if ($count['total'] > count($data)) {
              if ($this->options['page'] > 1) {
                  $result['paging']['prev'] = HTTPS_SERVER.DIR_WS_CATALOG.ltrim($this->options['path'], '/').'?'.xtc_get_all_get_params(array('page')).'page='.($this->options['page'] - 1);
              }
              if (((($this->options['page'] - 1) * $this->options['limit']) + $this->options['limit']) < $count['total']) {
                  $result['paging']['next'] = HTTPS_SERVER.DIR_WS_CATALOG.ltrim($this->options['path'], '/').'?'.xtc_get_all_get_params(array('page')).'page='.($this->options['page'] + 1);
              }
          }
          
          return $result;
      }
    
  }
