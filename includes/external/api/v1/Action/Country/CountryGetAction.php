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
          if (isset($this->options['iso2']) && preg_replace('/[^\w\,]/', '', $this->options['iso2']) != '') {
              $data = preg_replace('/[^\w\,]/', '', $this->options['iso2']);
              $conditions[] = " countries_iso_code_2 IN ('".str_replace(',', "','", $data)."') ";
          }
          if (isset($this->options['iso3']) && preg_replace('/[^\w\,]/', '', $this->options['iso3']) != '') {
              $data = preg_replace('/[^\w\,]/', '', $this->options['iso3']);
              $conditions[] = " countries_iso_code_3 IN ('".str_replace(',', "','", $data)."') ";
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

      /**
       * Read a geo zone by the given geo zone id.
       *
       * @param int $geoZoneId The category id
       *
       * @throws Exception
       *
       * @return array The geo zone data
       */
      public function GetSingleGeoZone(int $geoZoneId): array
      {
          // Input validation
          if (empty($geoZoneId)) {
              throw new Exception('Geo Zone ID required');
          }
          
          $geo_zone_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_GEO_ZONES."
                                           WHERE geo_zone_id = '".(int)$geoZoneId."'");
          if (xtc_db_num_rows($geo_zone_query) < 1) {
              throw new Exception(sprintf('Geo Zone not found: %s', $geoZoneId));
          } else {
              $geo_zone = xtc_db_fetch_array($geo_zone_query);
              $geo_zone['geo_zone_name'] = $this->parse_multi_language_value($geo_zone['geo_zone_name']);
              $geo_zone['geo_zone_description'] = $this->parse_multi_language_value($geo_zone['geo_zone_description']);
          }
          
          $result = $this->encode_request($geo_zone);
          return $result;
      }

      /**
       * Read geo zone by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The geo zone data
       */
      public function GetGeoZones(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                                                        
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_GEO_ZONES);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Geo Zone found');
          }
          
          $data = [];
          $geo_zone_query = xtc_db_query("SELECT geo_zone_id
                                            FROM ".TABLE_GEO_ZONES."
                                        ORDER BY geo_zone_id ASC
                                           LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($geo_zone = xtc_db_fetch_array($geo_zone_query)) {
              $data[] = $this->GetSingleGeoZone($geo_zone['geo_zone_id']);
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

      /**
       * Read a tax class by the given tax class id.
       *
       * @param int $taxClassId The category id
       *
       * @throws Exception
       *
       * @return array The tax class data
       */
      public function GetSingleTaxClass(int $taxClassId): array
      {
          // Input validation
          if (empty($taxClassId)) {
              throw new Exception('Tax Class ID required');
          }
          
          $tax_class_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_TAX_CLASS."
                                            WHERE tax_class_id = '".(int)$taxClassId."'");
          if (xtc_db_num_rows($tax_class_query) < 1) {
              throw new Exception(sprintf('Tax Class not found: %s', $taxClassId));
          } else {
              $tax_class = xtc_db_fetch_array($tax_class_query);
              $tax_class['tax_class_title'] = $this->parse_multi_language_value($tax_class['tax_class_title']);
              $tax_class['tax_class_description'] = $this->parse_multi_language_value($tax_class['tax_class_description']);
          }
          
          $result = $this->encode_request($tax_class);
          return $result;
      }

      /**
       * Read geo zone by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The tax class data
       */
      public function GetTaxClass(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                                                        
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_TAX_CLASS);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Tax Class found');
          }
          
          $data = [];
          $tax_class_query = xtc_db_query("SELECT tax_class_id
                                             FROM ".TABLE_TAX_CLASS."
                                         ORDER BY tax_class_id ASC
                                            LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($tax_class = xtc_db_fetch_array($tax_class_query)) {
              $data[] = $this->GetSingleTaxClass($tax_class['tax_class_id']);
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

      /**
       * Read a tax rates by the given tax rates id.
       *
       * @param int $taxClassId The category id
       *
       * @throws Exception
       *
       * @return array The tax rates data
       */
      public function GetSingleTaxRate(int $taxClassId): array
      {
          // Input validation
          if (empty($taxClassId)) {
              throw new Exception('Tax Class ID required');
          }
          
          $tax_rates_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_TAX_RATES."
                                            WHERE tax_rates_id = '".(int)$taxClassId."'");
          if (xtc_db_num_rows($tax_rates_query) < 1) {
              throw new Exception(sprintf('Tax Rate not found: %s', $taxClassId));
          } else {
              $tax_rates = xtc_db_fetch_array($tax_rates_query);
              $tax_rates['tax_rates_title'] = $this->parse_multi_language_value($tax_rates['tax_rates_title']);
              $tax_rates['tax_rates_description'] = $this->parse_multi_language_value($tax_rates['tax_rates_description']);
          }
          
          $result = $this->encode_request($tax_rates);
          return $result;
      }

      /**
       * Read geo zone by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The tax rates data
       */
      public function GetTaxRates(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                                                        
          $conditions = [];
          if (isset($this->options['zone']) && preg_replace('/[^\d\,]/', '', $this->options['zone']) != '') {
              $conditions[] = " tax_zone_id IN (".preg_replace('/[^\d\,]/', '', $this->options['zone']).") ";
          }
          if (isset($this->options['class']) && preg_replace('/[^\d\,]/', '', $this->options['class']) != '') {
              $conditions[] = " tax_class_id IN (".preg_replace('/[^\d\,]/', '', $this->options['class']).") ";
          }

          $where = '';
          if (count($conditions) > 0) {
              $where = " WHERE ".implode(' AND ', $conditions);
          }

          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_TAX_RATES."
                                              ".$where);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Tax Rate found');
          }
          
          $data = [];
          $tax_rates_query = xtc_db_query("SELECT tax_rates_id
                                             FROM ".TABLE_TAX_RATES."
                                                  ".$where."
                                         ORDER BY tax_rates_id ASC
                                            LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($tax_rates = xtc_db_fetch_array($tax_rates_query)) {
              $data[] = $this->GetSingleTaxClass($tax_rates['tax_rates_id']);
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
