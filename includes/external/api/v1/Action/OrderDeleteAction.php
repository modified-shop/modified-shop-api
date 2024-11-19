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
  trait OrderDeleteAction
  {
      /**
       * Delete an order by the given order id.
       *
       * @param int $orderId The order id
       *
       * @return void
       */
      public function DeleteOrder(int $orderId): void
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
              
              $this->DeleteAllProduct($orderId);
              $this->DeleteAllStatusHistory($orderId);
              $this->DeleteAllTotal($orderId);
              $this->DeleteAllTracking($orderId);

              //delete
              xtc_db_query("DELETE FROM ".TABLE_ORDERS." WHERE orders_id = '".(int)$orderId."'");
          }

          $this->logger->info(sprintf('Order deleted successfully: %s', $orderId));
      }
      
      /**
       * Delete a product by the given order id and order products id.
       *
       * @param int $orderId The order id
       * @param int $orderProductsId The order products id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteProduct(int $orderId, int $orderProductsId): void
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $where = '';
          if ($orderProductsId > 0) {
              $where = "AND orders_products_id = '".(int)$orderProductsId."'";
          }

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS_PRODUCTS."
                                        WHERE orders_id = '".(int)$orderId."'
                                              ".$where);
          if (xtc_db_num_rows($order_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Order products not found: %s', $orderId));
          } else {
              while ($order = xtc_db_fetch_array($order_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." 
                                      WHERE orders_id = '".(int)$orderId."'
                                        AND orders_products_id = '".(int)$order['orders_products_id']."'");

                  xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." 
                                      WHERE orders_id = '".(int)$orderId."'
                                        AND orders_products_id = '".(int)$order['orders_products_id']."'");

                  xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS." 
                                      WHERE orders_id = '".(int)$orderId."'
                                        AND orders_products_id = '".(int)$order['orders_products_id']."'");
              }
          }
      }

      /**
       * Delete all product by the given order id.
       *
       * @param int $orderId The order id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllProduct(int $orderId): void
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $this->DeleteProduct($orderId, 0);
      }

      /**
       * Delete a product attributes by the given order id and order products attributes id.
       *
       * @param int $orderId The order id
       * @param int $orderProductsAttributesId The order products attributes id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteProductAttributes(int $orderId, int $orderProductsAttributesId): void
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $where = '';
          if ($orderProductsAttributesId > 0) {
              $where = "AND orders_products_attributes_id = '".(int)$orderProductsAttributesId."'";
          }

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
                                        WHERE orders_id = '".(int)$orderId."'
                                              ".$where);
          if (xtc_db_num_rows($order_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Order products attributes not found: %s', $orderId));
          } else {
              while ($order = xtc_db_fetch_array($order_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." 
                                      WHERE orders_id = '".(int)$orderId."'
                                        AND orders_products_attributes_id = '".(int)$order['orders_products_attributes_id']."'");
              }
          }
      }

      /**
       * Delete a product download by the given order id and order products download id.
       *
       * @param int $orderId The order id
       * @param int $orderProductsDownloadId The order products download id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteProductDownload(int $orderId, int $orderProductsDownloadId): void
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $where = '';
          if ($orderProductsDownloadId > 0) {
              $where = "AND orders_products_download_id = '".(int)$orderProductsDownloadId."'";
          }

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS_PRODUCTS_DOWNLOAD."
                                        WHERE orders_id = '".(int)$orderId."'
                                              ".$where);
          if (xtc_db_num_rows($order_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Order products download not found: %s', $orderId));
          } else {
              while ($order = xtc_db_fetch_array($order_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." 
                                      WHERE orders_id = '".(int)$orderId."'
                                        AND orders_products_download_id = '".(int)$order['orders_products_download_id']."'");
              }
          }
      }

      /**
       * Delete a status history by the given order id and order status history id.
       *
       * @param int $orderId The order id
       * @param int $orderStatusHistoryId The order status history id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteStatusHistory(int $orderId, int $orderStatusHistoryId): void
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $where = '';
          if ($orderStatusHistoryId > 0) {
              $where = "AND orders_status_history_id = '".(int)$orderStatusHistoryId."'";
          }

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS_STATUS_HISTORY."
                                        WHERE orders_id = '".(int)$orderId."'
                                              ".$where);
          if (xtc_db_num_rows($order_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Order status history not found: %s', $orderId));
          } else {
              while ($order = xtc_db_fetch_array($order_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_ORDERS_STATUS_HISTORY." 
                                      WHERE orders_id = '".(int)$orderId."'
                                        AND orders_status_history_id = '".(int)$order['orders_status_history_id']."'");
              }
          }
      }

      /**
       * Delete all status history by the given order id.
       *
       * @param int $orderId The order id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllStatusHistory(int $orderId): void
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $this->DeleteStatusHistory($orderId, 0);
      }

      /**
       * Delete a order total by the given order id and order total id.
       *
       * @param int $orderId The order id
       * @param int $orderTotalId The order total id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteTotal(int $orderId, int $orderTotalId): void
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $where = '';
          if ($orderTotalId > 0) {
              $where = "AND orders_total_id = '".(int)$orderTotalId."'";
          }

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS_TOTAL."
                                        WHERE orders_id = '".(int)$orderId."'
                                              ".$where);
          if (xtc_db_num_rows($order_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Order total not found: %s', $orderId));
          } else {
              while ($order = xtc_db_fetch_array($order_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_ORDERS_TOTAL." 
                                      WHERE orders_id = '".(int)$orderId."'
                                        AND orders_total_id = '".(int)$order['orders_total_id']."'");
              }
          }
      }

      /**
       * Delete all order total by the given order id.
       *
       * @param int $orderId The order id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllTotal(int $orderId): void
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $this->DeleteTotal($orderId, 0);
      }

      /**
       * Delete a order tracking by the given order id and tracking id.
       *
       * @param int $orderId The order id
       * @param int $trackingId The tracking id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteTracking(int $orderId, int $trackingId): void
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $where = '';
          if ($trackingId > 0) {
              $where = "AND tracking_id = '".(int)$trackingId."'";
          }

          $order_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS_TRACKING."
                                        WHERE orders_id = '".(int)$orderId."'
                                              ".$where);
          if (xtc_db_num_rows($order_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Order tracking not found: %s', $orderId));
          } else {
              while ($order = xtc_db_fetch_array($order_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_ORDERS_TRACKING." 
                                      WHERE orders_id = '".(int)$orderId."'
                                        AND tracking_id = '".(int)$order['tracking_id']."'");
              }
          }
      }

      /**
       * Delete all order tracking by the given order id.
       *
       * @param int $orderId The order id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllTracking(int $orderId): void
      {
          // Input validation
          if (empty($orderId)) {
              throw new Exception('Order ID required');
          }

          $this->DeleteTracking($orderId, 0);
      }

  }
