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
  trait AttributesGetAction
  {
      /**
       * Read an option by the given option id.
       *
       * @param int $optionId The option id
       *
       * @throws Exception
       *
       * @return array The option data
       */
      public function GetSingleOption(int $optionId): array
      {
          // Input validation
          if (empty($optionId)) {
              throw new Exception('Option ID required');
          }
          
          $option = [];
          $option_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_OPTIONS."
                                          WHERE products_options_id = '".(int)$optionId."'");
          if (xtc_db_num_rows($option_query) < 1) {
              throw new Exception(sprintf('Option not found: %s', $optionId));
          } else {
              $options_query = xtc_db_query("SELECT po.*,
                                                    l.code
                                               FROM ".TABLE_PRODUCTS_OPTIONS." po
                                               JOIN ".TABLE_LANGUAGES." l
                                                    ON l.languages_id = po.language_id
                                              WHERE po.products_options_id = '".(int)$optionId."'");
              while ($options = xtc_db_fetch_array($options_query)) {
                  $code = $options['code'];
                  unset($options['code']);

                  $option[$code] = $options;
              }
          }

          $result = $this->encode_request($option);
          return $result;          
      }

      /**
       * Read options by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The options data
       */
      public function GetOptions($options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                    
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_PRODUCTS_OPTIONS);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Product options found');
          }
          
          $data = [];
          $options_query = xtc_db_query("SELECT products_options_id
                                           FROM ".TABLE_PRODUCTS_OPTIONS."
                                       ORDER BY products_options_sortorder DESC, products_options_id
                                          LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($options = xtc_db_fetch_array($options_query)) {
              $data[] = $this->GetSingleOption($options['products_options_id']);
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
