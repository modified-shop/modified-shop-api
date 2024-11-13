<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // orders
  $app->get('/orders',                              \api\v1\Service\Order\GetOrders::class);
  $app->get('/orders/{id}',                         \api\v1\Service\Order\GetSingleOrder::class);
  $app->get('/orders/{id}/products',                \api\v1\Service\Order\GetOrderProducts::class);
  $app->get('/orders/{id}/products_attributes',     \api\v1\Service\Order\GetOrderProductsAttributes::class);
  $app->get('/orders/{id}/products_download',       \api\v1\Service\Order\GetOrderProductsDownload::class);
  $app->get('/orders/{id}/status_history',          \api\v1\Service\Order\GetOrderStatusHistory::class);
  $app->get('/orders/{id}/total',                   \api\v1\Service\Order\GetOrderTotal::class);
  $app->get('/orders/{id}/tracking',                \api\v1\Service\Order\GetOrderTracking::class);
  
  // update orders
  $app->post('/orders/status/{id}',                 \api\v1\Service\Order\UpdateOrderStatus::class);
  $app->post('/orders/tracking/{id}',               \api\v1\Service\Order\InsertOrderTracking::class);

  // delete orders
  $app->delete('/orders/{id}',                                \api\v1\Service\Order\DeleteOrder::class);
  $app->delete('/orders/{id}/products/{pid}',                 \api\v1\Service\Order\DeleteProduct::class);
  $app->delete('/orders/{id}/products_attributes/{aid}',      \api\v1\Service\Order\DeleteProductAttributes::class);
  $app->delete('/orders/{id}/products_download/{did}',        \api\v1\Service\Order\DeleteProductDownload::class);
  $app->delete('/orders/{id}/status_history/{hid}',           \api\v1\Service\Order\DeleteStatusHistory::class);
  $app->delete('/orders/{id}/total/{tid}',                    \api\v1\Service\Order\DeleteTotal::class);
  $app->delete('/orders/{id}/tracking/{tid}',                 \api\v1\Service\Order\DeleteTracking::class);
