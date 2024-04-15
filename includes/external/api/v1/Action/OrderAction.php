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

  use api\v1\Action\BaseAction;
  use Exception;

  // include needed classes
  require_once(DIR_WS_CLASSES.'order.php');
  require_once(DIR_WS_CLASSES.'xtcPrice.php');

  // include needed functions
  require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
  require_once(DIR_FS_INC.'xtc_catalog_href_link.inc.php');
  require_once(DIR_FS_INC.'get_tracking_link.inc.php');

  /**
   * Service.
   */
  final class OrderAction extends BaseAction
  {
      /**
       * Read an order by the given order id.
       *
       * @param int $orderId The order id
       *
       * @throws Exception
       *
       * @return array The order data
       */
      public function GetSingleOrder(int $orderId): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $order = new \order($orderId);

          if (!isset($order->info['orders_id'])) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          }
          
          $order->info['transaction_id'] = $this->getTrasactionID($order->info['orders_id'], $order->info['payment_method']);
          
          $result = [
            'info' => $order->info,
            'customer' => $order->customer,
            'delivery' => $order->delivery,
            'billing' => $order->billing,
            'products' => $order->products,
            'totals' => $order->totals,
          ];
          
          $result = $this->encode_request($result);
          return $result;
      }

      /**
       * Read orders by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The order data
       */
      public function GetAllOrders($options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
          
          $conditions = [];
          if (!empty(preg_replace('/[^\d\,]/', '', $this->options['status']))) {
              $conditions[] = " orders_status IN (".preg_replace('/[^\d\,]/', '', $this->options['status']).") ";
          }
          if ((int)$this->options['from'] > 0) {
              $conditions[] = " date_purchased >= '".date('Y-m-d H:i:s', (int)$this->options['from'])."' ";
          }
          if ((int)$this->options['to'] > 0) {
              $conditions[] = " date_purchased <= '".date('Y-m-d H:i:s', (int)$this->options['to'])."' ";
          }
          
          if (count($conditions) > 0) {
            $where = " WHERE ".implode(' AND ', $conditions);
          }
          
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_ORDERS."
                                              ".$where);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Order found');
          }
          
          $data = [];
          $orders_query = xtc_db_query("SELECT orders_id
                                          FROM ".TABLE_ORDERS."
                                               ".$where."
                                      ORDER BY date_purchased DESC
                                         LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($orders = xtc_db_fetch_array($orders_query)) {
              $data[] = $this->GetSingleOrder($orders['orders_id']);
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
       * Set order status by the given order id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return void
       */
      public function UpdateOrderStatus(int $orderId, array $options): void
      {
          global $xtPrice;
          
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $order = new \order($orderId);

          if (!isset($order->info['orders_id'])) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if (empty($this->options['orders_status_id'])) {
              throw new Exception('Orders Status ID required');
          }
          
          error_log(print_r($this->options, true), 3, DIR_FS_CATALOG.'log/api.log');
          
          $lang_query = xtc_db_query("SELECT *
                                        FROM ".TABLE_LANGUAGES."
                                       WHERE directory = '".xtc_db_input($order->info['language'])."'");
          $lang_array = xtc_db_fetch_array($lang_query);
          $lang = $lang_array['languages_id'];
          $lang_code = $lang_array['code'];
          $lang_charset = $lang_array['language_charset'];

          $orders_status_lang_array = [];
          $orders_status_query = xtc_db_query("SELECT orders_status_id,
                                                      orders_status_name,
                                                      language_id
                                                 FROM ".TABLE_ORDERS_STATUS."
                                             ORDER BY sort_order");
          while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
              $orders_status_lang_array[$orders_status['language_id']][$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
          }

          if (!isset($orders_status_lang_array[$lang][$this->options['orders_status_id']])) {
              throw new Exception('Orders Status ID not valid');
          }

          if (isset($this->options['documents'])
              && is_array($this->options['documents'])
              )
          {
            foreach ($this->options['documents'] as $documents) {
              file_put_contents(DIR_FS_CATALOG.DIR_ADMIN.'archives/invoice/'.$documents['title'], base64_encode($documents['data']));
            }
          }
          
          $tracking_array = [];
          if (isset($this->options['tracking'])) {
              $tracking_array = $this->InsertOrderTracking($orderId, $options);              
          }
          
          $smarty = new \Smarty();    
          $xtPrice = new \xtcPrice($order->info['currency'], $order->info['status']);
          
          $oID = $orderId;
          $status = (int)$this->options['orders_status_id'];
          $comments = '';
          if (!empty($this->options['comments'])) {
              $comments = $this->options['comments'];
          }

          $_POST['notify'] = 'off';
          if (!empty($this->options['customer_notified'])) {
              $_POST['notify'] = (int)$this->options['customer_notified'] == 1 ? 'on' : 'off';
          }
          $_POST['notify_comments'] = 'off';
          if (!empty($this->options['comments_sent'])) {
              $_POST['notify_comments'] = (int)$this->options['comments_sent'] == 1 ? 'on' : 'off';
          }

          $_POST['tracking_id'] = array_column($tracking_array, 'tracking_id');
          define('_VALID_XTC', true);
          $order_updated = false;
          $email_preview = false;
          
          require_once(DIR_FS_CATALOG.DIR_ADMIN.'includes/filenames.php');
          require_once(DIR_WS_LANGUAGES.$order->info['language'].'/admin/'.$order->info['language'].'.php');
          include(DIR_FS_CATALOG.DIR_ADMIN.'includes/modules/orders_update.php');
      
          if ($order_updated) {
              $this->logger->info(sprintf('Order updated successfully: %s', $orderId));
          }
      }

      /**
       * Set order status by the given order id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The tracking data
       */
      public function InsertOrderTracking(int $orderId, array $options): array
      {
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $order = new \order($orderId);

          if (!isset($order->info['orders_id'])) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if (isset($this->options['tracking'])) {
              foreach ($this->options['tracking'] as $index => $tracking) {
                  if (isset($tracking['carrier']) 
                      && !empty($tracking['carrier'])
                      && !isset($tracking['carrier_id'])
                      )
                  {
                    $carrier_query = xtc_db_query("SELECT *
                                                     FROM ".TABLE_CARRIERS."
                                                    WHERE carrier_name = '".xtc_db_input($tracking['carrier'])."'");
                    $carrier = xtc_db_fetch_array($carrier_query);
                    $this->options['tracking'][$index]['carrier'] = $tracking['carrier_id'] = $carrier['carrier_id'];
                  }

                  if (empty($tracking['carrier_id'])) {
                      throw new Exception('Carrier ID required');
                  }

                  if (empty($tracking['carrier_id'])) {
                      throw new Exception('Parcel ID required');
                  }

                  $check_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_ORDERS_TRACKING."
                                                 WHERE parcel_id = '".xtc_db_input($tracking['parcel_id'])."'
                                                   AND orders_id = '".(int)$orderId."'");
                  if (xtc_db_num_rows($check_query) < 1) {
                      $sql_data_array = array(
                          'orders_id' => $orderId,
                          'carrier_id' => $tracking['carrier_id'],
                          'parcel_id' => $tracking['parcel_id'],
                          'date_added' => 'now()'
                      );
                      xtc_db_perform(TABLE_ORDERS_TRACKING, $sql_data_array);
                      $this->options['tracking'][$index]['tracking_id'] = xtc_db_insert_id();
                  } else {
                    $check = xtc_db_fetch_array($check_query);
                    $this->options['tracking'][$index]['tracking_id'] = $check['tracking_id'];
                  }
              }
          } else {
            throw new Exception('Tracking required');
          }

          return $this->options['tracking'];
      }
      
      /**
       * Delete an order by the given order id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteOrder(int $orderId, array $options): void
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
          
          $restock = false;
          if (isset($this->options['restock'])) {
              $restock = (($this->options['restock'] == 1) ? 'on' : false);
          }
          $activate = true;
          if (isset($this->options['activate'])) {
              $activate = (($this->options['activate'] == 1) ? true : false);
          }
                    
          xtc_remove_order($orderId, $restock, $activate);
          
          $this->logger->info(sprintf('Order deleted successfully: %s', $orderId));
      }
      
      /**
       * Delete an order by the given order id.
       *
       * @param int $orderId The order id
       * @param string $payment_method 
       *
       * @return string
       */
      private function getTrasactionID(int $orderId, string $payment_method): string
      {
          $transaction_id = '';

          switch ($payment_method) {
            case 'paypal':
            case 'paypalacdc':
            case 'paypalpui':
            case 'paypalexpress':
            case 'paypalsepa':
            case 'paypalsofort':
            case 'paypalgiropay':
              $check_query = xtc_db_query("SELECT *
                                             FROM `paypal_payment`
                                            WHERE orders_id = '".(int)$orderId."'");
              if (xtc_db_num_rows($check_query) > 0) {
                $check = xtc_db_fetch_array($check_query);
                $transaction_id = (string)$check['transaction_id'];
              }
              break;
          }
          
          return $transaction_id;
      }

  }
