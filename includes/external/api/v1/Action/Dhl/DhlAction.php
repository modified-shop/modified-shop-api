<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Dhl;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;

  // include needed classes
  require_once(DIR_FS_EXTERNAL.'dhl/DHLBusinessShipment.php');
  
  /**
   * Service.
   */
  final class DhlAction extends BaseAction
  {
      /**
       * Create a DHL parcel for the given order id.
       *
       * @param int $orderId The order id
       *
       * @throws Exception
       *
       * @return array The DHL data
       */
      public function GetDhl(int $orderId, array $options): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $order = new \order($orderId);

          if (!isset($order->info['orders_id'])) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          }
          
          $this->options = [
              'type' => ((MODULE_DHL_PRODUCT == 'Paket') ? 0 : 1),
              'codeable' => ((MODULE_DHL_CODING == 'True') ? true : false),
              'insurance' => 1,
              'retoure' => ((MODULE_DHL_RETOURE == 'True') ? true : false),
              'avs' => MODULE_DHL_AVS,
              'personal' => ((MODULE_DHL_PERSONAL == 'True') ? true : false),
              'no_neighbour' => ((MODULE_DHL_NO_NEIGHBOUR == 'True') ? true : false),
              'ident' => MODULE_DHL_IDENT,
              'dob' => ((strtotime($order->customer['dob']) > 0 && strtotime($order->customer['dob']) != false) ? date('d.m.Y', strtotime($order->customer['dob'])) : ''),
              'bulky' => ((MODULE_DHL_BULKY == 'True') ? true : false),
              'parcel_outlet' => ((MODULE_DHL_PARCEL_OUTLET == 'True') ? true : false),
              'premium' => ((MODULE_DHL_PREMIUM == 'True') ? true : false),
          ];

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          $dhl = new \DHLBusinessShipment($this->options);
          if (!isset($dhl->weight)) {
            $dhl->weight = $dhl->calculate_weight($orderId);
          }
          $response = $dhl->CreateLabel($orderId);

          if (is_array($response) && isset($response['message'])) {
              if (is_array($response['message'])) {
                  foreach ($response['message'] as $message) {
                      throw new Exception($message);
                  }
              } else {
                  throw new Exception($response['message']);
              }
          }

          $result_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_ORDERS_TRACKING."
                                         WHERE parcel_id = '".xtc_db_input($response)."'
                                           AND dhl_label_url != ''");
          $result = xtc_db_fetch_array($result_query);
                  
          return $result;
      }
      
      /**
       * Delete a DHL parcel by the given parcel id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteDhl(int $orderId, array $options): void
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $order = new \order($orderId);

          if (!isset($order->info['orders_id'])) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          $result = [];
          if (isset($this->options['tracking_id'])) {
              $result_query = xtc_db_query("SELECT *
                                              FROM ".TABLE_ORDERS_TRACKING."
                                             WHERE tracking_id = '".(int)$this->options['tracking_id']."'
                                               AND orders_id = '".(int)$orderId."'");
          } elseif (isset($this->options['parcel_id'])) {
              $result_query = xtc_db_query("SELECT *
                                              FROM ".TABLE_ORDERS_TRACKING."
                                             WHERE parcel_id = '".xtc_db_input($this->options['parcel_id'])."'
                                               AND orders_id = '".(int)$orderId."'");
          }
          $result = xtc_db_fetch_array($result_query);

          if (count($result) < 1) {
              throw new Exception(sprintf('Tracking not found'));
          }
                    
          $dhl = new \DHLBusinessShipment(array());
          $response = $dhl->DeleteLabel($result['parcel_id']);

          if (is_array($response) && isset($response['message'])) {
              if (is_array($response['message'])) {
                  foreach ($response['message'] as $message) {
                      throw new Exception($message);
                  }
              } else {
                  throw new Exception($response['message']);
              }
          }
          
          xtc_db_query("DELETE FROM ".TABLE_ORDERS_TRACKING." 
                              WHERE tracking_id = '".(int)$result['tracking_id']."'");
          
          $this->logger->info(sprintf('Tracking deleted successfully: %s', $result['parcel_id']));
      }

  }
