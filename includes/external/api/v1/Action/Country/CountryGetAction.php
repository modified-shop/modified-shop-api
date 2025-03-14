<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Country;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  trait CountryGetAction
  {
      /**
       * Read a country by the given country id.
       *
       * @param int $countryId The category id
       *
       * @throws Exception
       *
       * @return array The country data
       */
      public function GetSingleCountry(int $countryId): array
      {
          // Input validation
          if (empty($countryId)) {
              throw new Exception('Country ID required');
          }
          
          $country_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_COUNTRIES."
                                          WHERE countries_id = '".(int)$countryId."'");
          if (xtc_db_num_rows($country_query) < 1) {
              throw new Exception(sprintf('Country not found: %s', $countryId));
          } else {
              $country = xtc_db_fetch_array($country_query);
          }
          
          $result = $this->encode_request($country);
          return $result;
      }

      /**
       * Read country by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The country data
       */
      public function GetCountries(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
          
          $conditions = [];
          if (isset($this->options['status']) && preg_replace('/[^\d\,]/', '', $this->options['status']) != '') {
              $conditions[] = " status IN (".preg_replace('/[^\d\,]/', '', $this->options['status']).") ";
          }
          if (isset($this->options['iso2']) && preg_replace('/[^\d\,]/', '', $this->options['iso2']) != '') {
              $conditions[] = " countries_iso_code_2 IN (".preg_replace('/[^\d\,]/', '', $this->options['iso2']).") ";
          }
          if (isset($this->options['iso3']) && preg_replace('/[^\d\,]/', '', $this->options['iso3']) != '') {
              $conditions[] = " countries_iso_code_3 IN (".preg_replace('/[^\d\,]/', '', $this->options['iso3']).") ";
          }
          
          $where = '';
          if (count($conditions) > 0) {
            $where = " WHERE ".implode(' AND ', $conditions);
          }
                                              
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_COUNTRIES."
                                              ".$where);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Country found');
          }
          
          $data = [];
          $countries_query = xtc_db_query("SELECT countries_id
                                             FROM ".TABLE_COUNTRIES."
                                                  ".$where."
                                         ORDER BY countries_id ASC
                                            LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($countries = xtc_db_fetch_array($countries_query)) {
              $data[] = $this->GetSingleCountry($countries['countries_id']);
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
