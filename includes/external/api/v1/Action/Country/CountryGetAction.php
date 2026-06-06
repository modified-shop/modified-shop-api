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
       * @param int $countryId The country id
       *
       * @throws Exception
       *
       * @return array The country data
       */
      public function GetCountryDetails(int $countryId): array
      {
          // Input validation
          if (empty($countryId)) {
              throw new Exception('Country ID required');
          }
          
          $country_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_COUNTRIES."
                                          WHERE countries_id = '".(int)$countryId."'");
          if (xtc_db_num_rows($country_query) < 1) {
              return $this->errormessage(sprintf('Country not found: %s', $countryId));
          } else {
              $country = xtc_db_fetch_array($country_query);

              if (isset($this->options['with'])) {
                  $with = explode(',', $this->options['with']);
                  if (in_array('zones', $with) !== false) {
                      $country['zones'] = $this->GetZones($countryId);
                  }
              }
          }
          
          $result = $this->encode_request($country);
          return $result;
      }

      /**
       * Read a country by the given country id.
       *
       * @param int $countryId The country id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The country data
       */
      public function GetSingleCountry(int $countryId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          // Input validation
          if (empty($countryId)) {
              throw new Exception('Country ID required');
          }

          $result = $this->GetCountryDetails($countryId);
          return $result;
      }

      /**
       * Read country by given conditions
       *
       * @param mixed[] $options
       *
       * @return array The country data
       */
      public function GetCountries(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
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
              return $this->errormessage('no Country found');
          }
          
          $data = [];
          $countries_query = xtc_db_query("SELECT countries_id
                                             FROM ".TABLE_COUNTRIES."
                                                  ".$where."
                                         ORDER BY countries_id ASC
                                            LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($countries = xtc_db_fetch_array($countries_query)) {
              $data[] = $this->GetCountryDetails($countries['countries_id']);
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
       * Read a zone by the given country id.
       *
       * @param int $countryId The country id
       *
       * @throws Exception
       *
       * @return array The geo zone data
       */
      public function GetZones($countryId) {

          // Input validation
          if (empty($countryId)) {
              throw new Exception('Country ID required');
          }
          
          $data = [];
          $zone_query = xtc_db_query("SELECT *
                                        FROM ".TABLE_ZONES."
                                       WHERE zone_country_id = '".(int)$countryId."'");
          if (xtc_db_num_rows($zone_query) > 0) {
              while ($zone = xtc_db_fetch_array($zone_query)) {
                  $data[] = $zone;
              }
          }

          $result = $this->encode_request($data);
          return $result;
      }
      
      /**
       * Read a geo zone by the given geo zone id.
       *
       * @param int $geoZoneId The geo zone id
       *
       * @throws Exception
       *
       * @return array The geo zone data
       */
      public function GetGeoZoneDetails(int $geoZoneId): array
      {
          // Input validation
          if (empty($geoZoneId)) {
              throw new Exception('Geo Zone ID required');
          }
          
          $geo_zone_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_GEO_ZONES."
                                           WHERE geo_zone_id = '".(int)$geoZoneId."'");
          if (xtc_db_num_rows($geo_zone_query) < 1) {
              return $this->errormessage(sprintf('Geo Zone not found: %s', $geoZoneId));
          } else {
              $geo_zone = xtc_db_fetch_array($geo_zone_query);
              $geo_zone['geo_zone_name'] = $this->parse_multi_language_value($geo_zone['geo_zone_name']);
              $geo_zone['geo_zone_description'] = $this->parse_multi_language_value($geo_zone['geo_zone_description']);

              if (isset($this->options['with'])) {
                  $with = explode(',', $this->options['with']);
                  if (in_array('countries', $with) !== false) {
                      $geo_zone['countries'] = $this->GetZonesCountries($geoZoneId);
                  }
              }
          }
          
          $result = $this->encode_request($geo_zone);
          return $result;
      }

      /**
       * Read a geo zone by the given geo zone id.
       *
       * @param int $geoZoneId The geo zone id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The geo zone data
       */
      public function GetSingleGeoZone(int $geoZoneId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          // Input validation
          if (empty($geoZoneId)) {
              throw new Exception('Geo Zone ID required');
          }
          
          $result = $this->GetGeoZoneDetails($geoZoneId);
          return $result;
      }

      /**
       * Read geo zone by given conditions
       *
       * @param mixed[] $options
       *
       * @return array The geo zone data
       */
      public function GetGeoZones(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
                                                        
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_GEO_ZONES);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              return $this->errormessage('no Geo Zone found');
          }
          
          $data = [];
          $geo_zone_query = xtc_db_query("SELECT geo_zone_id
                                            FROM ".TABLE_GEO_ZONES."
                                        ORDER BY geo_zone_id ASC
                                           LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($geo_zone = xtc_db_fetch_array($geo_zone_query)) {
              $data[] = $this->GetGeoZoneDetails($geo_zone['geo_zone_id']);
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
       * Read a zone countries by the given geo zone id.
       *
       * @param int $countryId The country id
       *
       * @throws Exception
       *
       * @return array The geo zone countries data
       */
      public function GetZonesCountries($geoZoneId) {

          // Input validation
          if (empty($geoZoneId)) {
              throw new Exception('Geo Zone ID required');
          }
          
          $data = [];
          $zone_query = xtc_db_query("SELECT zone_country_id
                                        FROM ".TABLE_ZONES_TO_GEO_ZONES."
                                       WHERE geo_zone_id = '".(int)$geoZoneId."'");
          if (xtc_db_num_rows($zone_query) > 0) {
              while ($zone = xtc_db_fetch_array($zone_query)) {
                  $data[] = $this->GetCountryDetails($zone['zone_country_id']);
              }
          }

          $result = $this->encode_request($data);
          return $result;
      }

      /**
       * Read a tax class by the given tax class id.
       *
       * @param int $taxClassId The class id
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
              return $this->errormessage(sprintf('Tax Class not found: %s', $taxClassId));
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
       * @return array The tax class data
       */
      public function GetTaxClass(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
                                                        
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_TAX_CLASS);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              return $this->errormessage('no Tax Class found');
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
       * @param int $taxRateId The tax rate id
       *
       * @throws Exception
       *
       * @return array The tax rates data
       */
      public function GetSingleTaxRate(int $taxRateId): array
      {
          // Input validation
          if (empty($taxRateId)) {
              throw new Exception('Tax Rate ID required');
          }
          
          $tax_rates_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_TAX_RATES."
                                            WHERE tax_rates_id = '".(int)$taxRateId."'");
          if (xtc_db_num_rows($tax_rates_query) < 1) {
              return $this->errormessage(sprintf('Tax Rate not found: %s', $taxRateId));
          } else {
              $tax_rates = xtc_db_fetch_array($tax_rates_query);
              $tax_rates['tax_description'] = $this->parse_multi_language_value($tax_rates['tax_description']);
          }
          
          $result = $this->encode_request($tax_rates);
          return $result;
      }

      /**
       * Read geo zone by given conditions
       *
       * @param mixed[] $options
       *
       * @return array The tax rates data
       */
      public function GetTaxRates(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
                                                        
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
              return $this->errormessage('no Tax Rate found');
          }
          
          $data = [];
          $tax_rates_query = xtc_db_query("SELECT tax_rates_id
                                             FROM ".TABLE_TAX_RATES."
                                                  ".$where."
                                         ORDER BY tax_rates_id ASC
                                            LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($tax_rates = xtc_db_fetch_array($tax_rates_query)) {
              $data[] = $this->GetSingleTaxRate($tax_rates['tax_rates_id']);
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
