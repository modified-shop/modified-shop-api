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
  final class OrderAction extends BaseAction
  {
      use OrderGetAction;
      use OrderDeleteAction;

      /**
       * Insert an order by the given options.
       *
       * @param mixed[] $options
       *
       * @return array The order data
       */
      public function InsertOrder(array $options): array
      {
          $order = $this->InsertUpdateOrder(0, $options);
          
          return $order;
      }

      /**
       * Insert or Update an order by the given order id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The order data
       */
      public function InsertUpdateOrder(int $orderId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if ($orderId > 0) {
              $action = 'update';
              $order_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_ORDERS."
                                            WHERE orders_id = '".(int)$orderId."'");
              if (xtc_db_num_rows($order_query) < 1) {
                  throw new Exception(sprintf('Order not found: %s', $orderId));
              } else {
                  $order = xtc_db_fetch_array($order_query);
                  $order['last_modified'] = 'now()';
              }
          } else {
              $action = 'insert';
              $order = $this->getDefaultTableValues(TABLE_ORDERS);
              $order['date_purchased'] = 'now()';
          }

          foreach ($order as $key => $value) {
              if (isset($this->options[$key])) {
                  $order[$key] = $this->options[$key];
              }
          }

          // Input validation
          $this->checkTableData(TABLE_ORDERS, $order);
          unset($order['orders_id']);

          xtc_db_perform(TABLE_ORDERS, $order, $action, "orders_id = '".(int)$orderId."'");
          if ($action == 'insert') {
              $orderId = xtc_db_insert_id();
          }

          return $this->GetOrder($orderId);
      }

      /**
       * Insert an order product by given order id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @return array The order product data
       */
      public function InsertOrderProduct(int $orderId, array $options): array
      {
          $order_product = $this->InsertUpdateOrderProduct($orderId, 0, $options);
          
          return $order_product;
      }

      /**
       * Insert or Update a order product by the given order id and order products id.
       *
       * @param int $orderId The order id
       * @param int $orderProductsId The order products id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The order product data
       */
      public function InsertUpdateOrderProduct(int $orderId, int $orderProductId, array $options): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          if (empty($orderProductId)) {
              throw new Exception('Order Product ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS."
                                        WHERE orders_id = '".(int)$orderId."'");
          if (xtc_db_num_rows($order_query) < 1) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          } else {          
              $order_product_query = xtc_db_query("SELECT *
                                                     FROM ".TABLE_ORDERS_PRODUCTS."
                                                    WHERE orders_id = '".(int)$orderId."'
                                                      AND orders_products_id = '".(int)$orderProductId."'");
              if (xtc_db_num_rows($order_product_query) > 0) {
                  $action = 'update';
                  $order_product = xtc_db_fetch_array($order_product_query);
              } else {
                  if (!isset($this->options['products_id'])) {
                      throw new Exception('Product ID required');
                  }
                  $action = 'insert';
                  $order_product = $this->getDefaultTableValues(TABLE_ORDERS_PRODUCTS);
                  $order_product['orders_id'] = (int)$orderId;
              }

              if (isset($this->options['products_id'])) {
                  $products_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_PRODUCTS."
                                                   WHERE products_id = '".(int)$this->options['products_id']."'");
                  if (xtc_db_num_rows($products_query) < 1) {
                      throw new Exception(sprintf('Product ID invalid'));
                  }
              }
          
              foreach ($order_product as $key => $value) {
                  if (isset($this->options[$key])) {
                      $order_product[$key] = $this->options[$key];
                  }
              }

              // Input validation
              $this->checkTableData(TABLE_ORDERS_PRODUCTS, $order_product);
              xtc_db_perform(TABLE_ORDERS_PRODUCTS, $order_product, $action, "orders_id = '".(int)$orderId."' AND orders_products_id = '".(int)$orderProductId."'");
          }

          return $this->GetOrderProducts($orderId);
      }

      /**
       * Insert an order product attributes by given order id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @return array The order product data
       */
      public function InsertOrderProductAttributes(int $orderId, array $options): array
      {
          $order_product = $this->InsertUpdateOrderProductAttributes($orderId, 0, $options);
          
          return $order_product;
      }

      /**
       * Insert or Update a order product attributes by the given order id and order products attributes id.
       *
       * @param int $orderId The order id
       * @param int $orderProductsAttributesId The order products attributes id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The order product data
       */
      public function InsertUpdateOrderProductAttributes(int $orderId, int $orderProductsAttributesId, array $options): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          if (empty($orderProductsAttributesId)) {
              throw new Exception('Order Product Attributes ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS."
                                        WHERE orders_id = '".(int)$orderId."'");
          if (xtc_db_num_rows($order_query) < 1) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          } else {          
              $order_product_attributes_query = xtc_db_query("SELECT *
                                                                FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
                                                               WHERE orders_id = '".(int)$orderId."'
                                                                 AND orders_products_attributes_id = '".(int)$orderProductsAttributesId."'");
              if (xtc_db_num_rows($order_product_attributes_query) > 0) {
                  $action = 'update';
                  $order_product_attributes = xtc_db_fetch_array($order_product_attributes_query);
              } else {
                  if (!isset($this->options['orders_products_id'])) {
                      throw new Exception('Order Product ID required');
                  }
                  $action = 'insert';
                  $order_product_attributes = $this->getDefaultTableValues(TABLE_ORDERS_PRODUCTS_ATTRIBUTES);
                  $order_product_attributes['orders_id'] = (int)$orderId;
              }

              if (isset($this->options['orders_products_id'])) {
                  $order_product_query = xtc_db_query("SELECT *
                                                         FROM ".TABLE_ORDERS_PRODUCTS."
                                                        WHERE orders_id = '".(int)$orderId."'
                                                          AND orders_products_id = '".(int)$this->options['orders_products_id']."'");
                  if (xtc_db_num_rows($order_product_query) < 1) {
                      throw new Exception('Order Product ID invalid');
                  }
              }
          
              foreach ($order_product_attributes as $key => $value) {
                  if (isset($this->options[$key])) {
                      $order_product_attributes[$key] = $this->options[$key];
                  }
              }

              // Input validation
              $this->checkTableData(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $order_product_attributes);
              xtc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $order_product_attributes, $action, "orders_id = '".(int)$orderId."' AND orders_products_attributes_id = '".(int)$orderProductsAttributesId."'");
          }

          return $this->GetOrderProducts($orderId);
      }

      /**
       * Insert an order product download by given order id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @return array The order product data
       */
      public function InsertOrderProductDownload(int $orderId, array $options): array
      {
          $order_product = $this->InsertUpdateOrderProductDownload($orderId, 0, $options);
          
          return $order_product;
      }

      /**
       * Insert or Update a order product attributes by the given order id and order products download id.
       *
       * @param int $orderId The order id
       * @param int $orderProductsDownloadId The order products download id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The order product data
       */
      public function InsertUpdateOrderProductDownload(int $orderId, int $orderProductsDownloadId, array $options): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          if (empty($orderProductsDownloadId)) {
              throw new Exception('Order Product Download ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS."
                                        WHERE orders_id = '".(int)$orderId."'");
          if (xtc_db_num_rows($order_query) < 1) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          } else {          
              $order_product_download_query = xtc_db_query("SELECT *
                                                              FROM ".TABLE_ORDERS_PRODUCTS_DOWNLOAD."
                                                             WHERE orders_id = '".(int)$orderId."'
                                                               AND orders_products_download_id = '".(int)$orderProductsDownloadId."'");
              if (xtc_db_num_rows($order_product_download_query) > 0) {
                  $action = 'update';
                  $order_product_download = xtc_db_fetch_array($order_product_download_query);
              } else {
                  if (!isset($this->options['orders_products_id'])) {
                      throw new Exception('Order Product ID required');
                  }
                  $action = 'insert';
                  $order_product_download = $this->getDefaultTableValues(TABLE_ORDERS_PRODUCTS_DOWNLOAD);
                  $order_product_download['orders_id'] = (int)$orderId;
              }

              if (isset($this->options['orders_products_id'])) {
                  $order_product_query = xtc_db_query("SELECT *
                                                         FROM ".TABLE_ORDERS_PRODUCTS."
                                                        WHERE orders_id = '".(int)$orderId."'
                                                          AND orders_products_id = '".(int)$this->options['orders_products_id']."'");
                  if (xtc_db_num_rows($order_product_query) < 1) {
                      throw new Exception('Order Product ID invalid');
                  }
              }
          
              foreach ($order_product_download as $key => $value) {
                  if (isset($this->options[$key])) {
                      $order_product_download[$key] = $this->options[$key];
                  }
              }

              // Input validation
              $this->checkTableData(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $order_product_download);
              xtc_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $order_product_download, $action, "orders_id = '".(int)$orderId."' AND orders_products_download_id = '".(int)$orderProductsDownloadId."'");
          }

          return $this->GetOrderProducts($orderId);
      }

      /**
       * Insert an order status history by given order id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @return array The order status history data
       */
      public function InsertOrderStatusHistory(int $orderId, array $options): array
      {
          $order_status_history = $this->InsertUpdateOrderStatusHistory($orderId, 0, $options);
          
          return $order_status_history;
      }

      /**
       * Insert or Update a order status history by the given order id and order status history id.
       *
       * @param int $orderId The order id
       * @param int $orderStatusHistoryId The order status history id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The order status history data
       */
      public function InsertUpdateOrderStatusHistory(int $orderId, int $orderStatusHistoryId, array $options): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          if (empty($orderStatusHistoryId)) {
              throw new Exception('Order Status History ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS."
                                        WHERE orders_id = '".(int)$orderId."'");
          if (xtc_db_num_rows($order_query) < 1) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          } else {          
              $order_status_history_query = xtc_db_query("SELECT *
                                                            FROM ".TABLE_ORDERS_STATUS_HISTORY."
                                                           WHERE orders_id = '".(int)$orderId."'
                                                             AND orders_status_history_id = '".(int)$orderStatusHistoryId."'");
              if (xtc_db_num_rows($order_status_history_query) > 0) {
                  $action = 'update';
                  $order_status_history = xtc_db_fetch_array($order_status_history_query);
              } else {
                  if (!isset($this->options['orders_status_id'])) {
                      throw new Exception('Order Status ID required');
                  }
                  $action = 'insert';
                  $order_status_history = $this->getDefaultTableValues(TABLE_ORDERS_STATUS_HISTORY);
                  $order_status_history['orders_id'] = (int)$orderId;
              }

              if (isset($this->options['orders_status_id'])) {
                  $order_status_query = xtc_db_query("SELECT *
                                                        FROM ".TABLE_ORDERS_STATUS."
                                                       WHERE orders_status_id = '".(int)$this->options['orders_status_id']."'");
                  if (xtc_db_num_rows($order_status_query) < 1) {
                      throw new Exception('Order Status ID invalid');
                  }
              }
          
              foreach ($order_status_history as $key => $value) {
                  if (isset($this->options[$key])) {
                      $order_status_history[$key] = $this->options[$key];
                  }
              }

              // Input validation
              $this->checkTableData(TABLE_ORDERS_STATUS_HISTORY, $order_status_history);
              xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $order_status_history, $action, "orders_id = '".(int)$orderId."' AND orders_status_history_id = '".(int)$orderStatusHistoryId."'");
          }

          return $this->GetOrderStatusHistory($orderId);
      }

      /**
       * Insert an order stotal by given order id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @return array The order total data
       */
      public function InsertOrderTotal(int $orderId, array $options): array
      {
          $order_total = $this->InsertUpdateOrderTotal($orderId, 0, $options);
          
          return $order_total;
      }

      /**
       * Insert or Update a order status history by the given order id and order status history id.
       *
       * @param int $orderId The order id
       * @param int $orderTotalId The order total id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The order total data
       */
      public function InsertUpdateOrderTotal(int $orderId, int $orderTotalId, array $options): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          if (empty($orderTotalId)) {
              throw new Exception('Order Total ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS."
                                        WHERE orders_id = '".(int)$orderId."'");
          if (xtc_db_num_rows($order_query) < 1) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          } else {          
              $order_total_query = xtc_db_query("SELECT *
                                                   FROM ".TABLE_ORDERS_TOTAL."
                                                  WHERE orders_id = '".(int)$orderId."'
                                                    AND orders_total_id = '".(int)$orderTotalId."'");
              if (xtc_db_num_rows($order_total_query) > 0) {
                  $action = 'update';
                  $order_total = xtc_db_fetch_array($order_total_query);
              } else {
                  $action = 'insert';
                  $order_total = $this->getDefaultTableValues(TABLE_ORDERS_TOTAL);
                  $order_total['orders_id'] = (int)$orderId;
              }
          
              foreach ($order_total as $key => $value) {
                  if (isset($this->options[$key])) {
                      $order_total[$key] = $this->options[$key];
                  }
              }

              // Input validation
              $this->checkTableData(TABLE_ORDERS_TOTAL, $order_total);
              xtc_db_perform(TABLE_ORDERS_TOTAL, $order_total, $action, "orders_id = '".(int)$orderId."' AND orders_total_id = '".(int)$orderTotalId."'");
          }

          return $this->GetOrderTotal($orderId);
      }

      /**
       * Insert an order tracking by given order id.
       *
       * @param int $orderId The order id
       * @param mixed[] $options
       *
       * @return array The order tracking data
       */
      public function InsertOrderTracking(int $orderId, array $options): array
      {
          $order_tracking = $this->InsertUpdateOrderTracking($orderId, 0, $options);
          
          return $order_tracking;
      }

      /**
       * Insert or Update a order status history by the given order id and order tracking id.
       *
       * @param int $orderId The order id
       * @param int $orderTrackingId The order tracking id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The order tracking data
       */
      public function InsertUpdateOrderTracking(int $orderId, int $orderTrackingId, array $options): array
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          if (empty($orderTrackingId)) {
              throw new Exception('Order Tracking ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS."
                                        WHERE orders_id = '".(int)$orderId."'");
          if (xtc_db_num_rows($order_query) < 1) {
              throw new Exception(sprintf('Order not found: %s', $orderId));
          } else {          
              $order_tracking_query = xtc_db_query("SELECT *
                                                      FROM ".TABLE_ORDERS_TRACKING."
                                                     WHERE orders_id = '".(int)$orderId."'
                                                       AND tracking_id = '".(int)$orderTrackingId."'");
              if (xtc_db_num_rows($order_tracking_query) > 0) {
                  $action = 'update';
                  $order_tracking = xtc_db_fetch_array($order_tracking_query);
              } else {
                  if (!isset($this->options['carrier_id'])) {
                      throw new Exception('Carrier ID required');
                  }
                  $action = 'insert';
                  $order_tracking = $this->getDefaultTableValues(TABLE_ORDERS_TRACKING);
                  $order_tracking['orders_id'] = (int)$orderId;
                  $order_tracking['date_added'] = 'now()';
              }

              if (isset($this->options['carrier_id'])) {
                  $carrier_query = xtc_db_query("SELECT *
                                                   FROM ".TABLE_CARRIERS."
                                                  WHERE carrier_id = '".(int)$this->options['carrier_id']."'");
                  if (xtc_db_num_rows($order_status_query) < 1) {
                      throw new Exception('Carrier ID invalid');
                  }
              }

              foreach ($order_tracking as $key => $value) {
                  if (isset($this->options[$key])) {
                      $order_tracking[$key] = $this->options[$key];
                  }
              }

              // Input validation
              $this->checkTableData(TABLE_ORDERS_TRACKING, $order_tracking);
              xtc_db_perform(TABLE_ORDERS_TRACKING, $order_tracking, $action, "orders_id = '".(int)$orderId."' AND tracking_id = '".(int)$orderTrackingId."'");
          }

          return $this->GetOrderTracking($orderId);
      }

  }
