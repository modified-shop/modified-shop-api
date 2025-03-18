<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Order;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;

  /**
   * Service.
   */
  trait OrderGetAction
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
      public function GetOrderDetails(int $orderId): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS."
                                        WHERE orders_id = '".(int)$orderId."'");
          if (xtc_db_num_rows($order_query) < 1) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          } else {            
              // disable Exception
              $this->throw_exception = false;
              
              $result = [
                  'orders' => $this->GetOrder($orderId),
              ];
              
              if (isset($this->options['with'])) {
                  $with = explode(',', $this->options['with']);
                  if (in_array('products', $with) !== false) {
                      $result['orders_products'] = $this->GetOrderProducts($orderId);
                  }
                  if (in_array('history', $with) !== false) {
                      $result['orders_status_history'] = $this->GetOrderStatusHistory($orderId);
                  }
                  if (in_array('total', $with) !== false) {
                      $result['orders_total'] = $this->GetOrderTotal($orderId);
                  }
                  if (in_array('tracking', $with) !== false) {
                      $result['orders_tracking'] = $this->GetOrderTracking($orderId);
                  }
              }
              
              return $result;
          }
      }

      /**
       * Read an order by the given order id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The order data
       */
      public function GetSingleOrder(int $orderId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $result = $this->GetOrderDetails($orderId);
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
      public function GetOrders(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
          
          $conditions = [];
          if (isset($this->options['status']) && !empty(preg_replace('/[^\d\,]/', '', $this->options['status']))) {
              $conditions[] = " orders_status IN (".preg_replace('/[^\d\,]/', '', $this->options['status']).") ";
          }
          if ((int)$this->options['from'] > 0) {
              $conditions[] = " date_purchased >= '".date('Y-m-d H:i:s', (int)$this->options['from'])."' ";
          }
          if ((int)$this->options['to'] > 0) {
              $conditions[] = " date_purchased <= '".date('Y-m-d H:i:s', (int)$this->options['to'])."' ";
          }
          
          $where = '';
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
              $data[] = $this->GetOrderDetails($orders['orders_id']);
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
       * Read a order by the given order id.
       *
       * @param int $orderId The order id
       *
       * @throws Exception
       *
       * @return array The order data
       */
      public function GetOrder(int $orderId): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $order = [];
          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS."
                                        WHERE orders_id = '".(int)$orderId ."'");
          if (xtc_db_num_rows($order_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          } else {
              $order = xtc_db_fetch_array($order_query);
          }

          $result = $this->encode_request($order);
          return $result;          
      }

      /**
       * Read a order products by the given order id.
       *
       * @param int $orderId The order id
       *
       * @throws Exception
       *
       * @return array The order data
       */
      public function GetOrderProducts(int $orderId): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $products = [];
          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS_PRODUCTS."
                                        WHERE orders_id = '".(int)$orderId."'");
          if (xtc_db_num_rows($order_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Order products not found: %s', $orderId));
          } else {
              $orders_products_query = xtc_db_query("SELECT *
                                                       FROM ".TABLE_ORDERS_PRODUCTS."
                                                      WHERE orders_id = '".(int)$orderId."'
                                                   ORDER BY orders_products_id");
              while ($orders_products = xtc_db_fetch_array($orders_products_query)) {
                  $orders_products['attributes'] = [];
                  $orders_products_attributes_query = xtc_db_query("SELECT *
                                                                      FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
                                                                     WHERE orders_id = '".(int)$orderId."'
                                                                       AND orders_products_id  = '".(int)$orders_products['orders_products_id']."'
                                                                  ORDER BY orders_products_attributes_id");
                  while ($orders_products_attributes = xtc_db_fetch_array($orders_products_attributes_query)) {
                      $orders_products['attributes'][] = $orders_products_attributes;
                  }

                  $orders_products['download'] = [];
                  $orders_products_download_query = xtc_db_query("SELECT *
                                                                    FROM ".TABLE_ORDERS_PRODUCTS_DOWNLOAD."
                                                                   WHERE orders_id = '".(int)$orderId."'
                                                                     AND orders_products_id  = '".(int)$orders_products['orders_products_id']."'
                                                                ORDER BY orders_products_download_id");
                  while ($orders_products_download = xtc_db_fetch_array($orders_products_download_query)) {
                      $orders_products['download'][] = $orders_products_download;
                  }
                 
                  $products[] = $orders_products;
              }
          }

          $result = $this->encode_request($products);
          return $result;          
      }

      /**
       * Read a order status history by the given order id.
       *
       * @param int $orderId The order id
       *
       * @throws Exception
       *
       * @return array The order data
       */
      public function GetOrderStatusHistory(int $orderId): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $status_history = [];
          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS_STATUS_HISTORY."
                                        WHERE orders_id = '".(int)$orderId."'");
          if (xtc_db_num_rows($order_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Order status history not found: %s', $orderId));
          } else {
              $orders_status_history_query = xtc_db_query("SELECT *
                                                             FROM ".TABLE_ORDERS_STATUS_HISTORY."
                                                            WHERE orders_id = '".(int)$orderId."'
                                                         ORDER BY orders_status_history_id");
              while ($orders_status_history = xtc_db_fetch_array($orders_status_history_query)) {
                  $status_history[] = $orders_status_history;
              }
          }

          $result = $this->encode_request($status_history);
          return $result;          
      }

      /**
       * Read a order total by the given order id.
       *
       * @param int $orderId The order id
       *
       * @throws Exception
       *
       * @return array The order data
       */
      public function GetOrderTotal(int $orderId): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $total = [];
          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS_TOTAL."
                                        WHERE orders_id = '".(int)$orderId."'");
          if (xtc_db_num_rows($order_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Order total not found: %s', $orderId));
          } else {
              $orders_total_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_ORDERS_TOTAL."
                                                   WHERE orders_id = '".(int)$orderId."'
                                                ORDER BY orders_total_id");
              while ($orders_total = xtc_db_fetch_array($orders_total_query)) {
                  $total[] = $orders_total;
              }
          }

          $result = $this->encode_request($total);
          return $result;          
      }

      /**
       * Read a order tracking by the given order id.
       *
       * @param int $orderId The order id
       *
       * @throws Exception
       *
       * @return array The order data
       */
      public function GetOrderTracking(int $orderId): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $total = [];
          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS_TRACKING."
                                        WHERE orders_id = '".(int)$orderId."'");
          if (xtc_db_num_rows($order_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Order tracking not found: %s', $orderId));
          } else {
              $orders_total_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_ORDERS_TRACKING."
                                                   WHERE orders_id = '".(int)$orderId."'
                                                ORDER BY tracking_id");
              while ($orders_total = xtc_db_fetch_array($orders_total_query)) {
                  $total[] = $orders_total;
              }
          }

          $result = $this->encode_request($total);
          return $result;          
      }

      /**
       * Read a order status by the given order status id.
       *
       * @param int $orderStatusId The order status id
       *
       * @throws Exception
       *
       * @return array The order status data
       */
      public function GetSingleOrderStatus(int $orderStatusId): array
      {
         // Input validation
          if (empty($orderStatusId)) {
              throw new Exception('Order Status ID required');
          }

          $order_status_query = xtc_db_query("SELECT os.*,
                                                     l.code
                                                FROM ".TABLE_ORDERS_STATUS." os
                                                JOIN ".TABLE_LANGUAGES." l
                                                     ON l.languages_id = os.language_id
                                               WHERE os.orders_status_id = '".(int)$orderStatusId."'");
          if (xtc_db_num_rows($order_status_query) < 1) {
              throw new Exception(sprintf('Order Status not found: %s', $orderStatusId));
          } else {
              $data = [];
              while ($order_status = xtc_db_fetch_array($order_status_query)) {
                  $code = $order_status['code'];
                  unset($order_status['code']);
              
                  $data[$code] = $order_status;
              }
          }

          $result = $this->encode_request($data);
          return $result;
      }

      /**
       * Read order status by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The order status data
       */
      public function GetOrderStatus(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                                                        
          $count_query = xtc_db_query("SELECT count(DISTINCT orders_status_id) as total
                                         FROM ".TABLE_ORDERS_STATUS);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Order Status found');
          }
          
          $data = [];
          $order_status_query = xtc_db_query("SELECT orders_status_id
                                                FROM ".TABLE_ORDERS_STATUS."
                                            GROUP BY orders_status_id
                                            ORDER BY orders_status_id ASC
                                               LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($order_status = xtc_db_fetch_array($order_status_query)) {
              $data[] = $this->GetSingleOrderStatus($order_status['orders_status_id']);
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
       * Read a order transaction by the given order id.
       *
       * @param int $orderId The order id
       * @param string $payment_method 
       *
       * @return string
       */
      private function GetTransactionID(int $orderId, string $payment_method): string
      {
          $transaction_id = '';

          switch ($payment_method) {
            case 'paypal':
            case 'paypalacdc':
            case 'paypalapplepay':
            case 'paypalbancontact':
            case 'paypalblik':
            case 'paypalcard':
            case 'paypalcart':
            case 'paypalclassic':
            case 'paypaleps':
            case 'paypalexpress':
            case 'paypalgiropay':
            case 'paypalgooglepay':
            case 'paypalideal':
            case 'paypallink':
            case 'paypalmybank':
            case 'paypalplus':
            case 'paypalpluslink':
            case 'paypalprzelewy':
            case 'paypalpui':
            case 'paypalsepa':
            case 'paypalsubscription':
            case 'paypalsofort':
            case 'paypaltrustly':
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
