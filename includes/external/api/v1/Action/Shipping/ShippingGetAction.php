<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Shipping;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  trait ShippingGetAction
  {
      /**
       * Read a carrier by the given carrier id.
       *
       * @param int $carrierID The carrier id
       *
       * @throws Exception
       *
       * @return array The carrier data
       */
      public function GetSingleCarrier(int $carrierID): array
      {
          // Input validation
          if (empty($carrierID)) {
              throw new Exception('Carrier ID required');
          }
          
          $carrier_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_CARRIERS."
                                          WHERE carrier_id = '".(int)$carrierID."'");
          if (xtc_db_num_rows($carrier_query) < 1) {
              throw new Exception(sprintf('Carrier not found: %s', $carrierID));
          } else {
              $carrier = xtc_db_fetch_array($carrier_query);
          }
          
          $result = $this->encode_request($carrier);
          return $result;
      }

      /**
       * Read carrier by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The carrier data
       */
      public function GetCarrier(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                                                        
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_CARRIERS);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Carrier found');
          }
          
          $data = [];
          $carrier_query = xtc_db_query("SELECT carrier_id
                                           FROM ".TABLE_CARRIERS."
                                       ORDER BY carrier_id ASC
                                          LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($carrier = xtc_db_fetch_array($carrier_query)) {
              $data[] = $this->GetSingleCarrier($carrier['carrier_id']);
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
       * Read a shipping status by the given shipping status id.
       *
       * @param int $shippingStatusId The shipping status id
       *
       * @throws Exception
       *
       * @return array The shipping status data
       */
      public function GetSingleShippingStatus(int $shippingStatusId): array
      {
          // Input validation
          if (empty($shippingStatusId)) {
              throw new Exception('Shipping Status ID required');
          }
          
          $shipping_status_query = xtc_db_query("SELECT ss.*,
                                                        l.code
                                                   FROM ".TABLE_SHIPPING_STATUS." ss
                                                   JOIN ".TABLE_LANGUAGES." l
                                                        ON l.languages_id = ss.language_id
                                                  WHERE ss.shipping_status_id = '".(int)$shippingStatusId."'");
          if (xtc_db_num_rows($shipping_status_query) < 1) {
              throw new Exception(sprintf('Shipping Status not found: %s', $shippingStatusId));
          } else {
              $shipping = [];
              while ($shipping_status = xtc_db_fetch_array($shipping_status_query)) {
                  $code = $shipping_status['code'];
                  unset($shipping_status['code']);
              
                  $shipping[$code] = $shipping_status;
              }
          }
          
          $result = $this->encode_request($shipping);
          return $result;
      }

      /**
       * Read carrier by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The shipping status data
       */
      public function GetShippingStatus(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                                                        
          $count_query = xtc_db_query("SELECT count(DISTINCT shipping_status_id) as total
                                         FROM ".TABLE_SHIPPING_STATUS);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Shipping Status found');
          }
          
          $data = [];
          $shipping_status_query = xtc_db_query("SELECT shipping_status_id
                                                   FROM ".TABLE_SHIPPING_STATUS."
                                               ORDER BY shipping_status_id ASC
                                                  LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($shipping_status = xtc_db_fetch_array($shipping_status_query)) {
              $data[] = $this->GetSingleShippingStatus($shipping_status['shipping_status_id']);
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
