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
  $app->get('/orders/carrier',                      \api\v1\Service\Order\GetOrderCarrier::class);
  $app->get('/orders/status',                       \api\v1\Service\Order\GetOrderStatus::class);
  $app->get('/orders/{id}',                         \api\v1\Service\Order\GetSingleOrder::class);
  $app->get('/orders/{id}/products',                \api\v1\Service\Order\GetOrderProducts::class);
  $app->get('/orders/{id}/status_history',          \api\v1\Service\Order\GetOrderStatusHistory::class);
  $app->get('/orders/{id}/total',                   \api\v1\Service\Order\GetOrderTotal::class);
  $app->get('/orders/{id}/tracking',                \api\v1\Service\Order\GetOrderTracking::class);
  
  // insert orders
  $app->post('/orders',                                      \api\v1\Service\Order\InsertOrder::class);
  $app->post('/orders/{id}/products',                        \api\v1\Service\Order\InsertOrderProduct::class);
  $app->post('/orders/{id}/products/{pid}',                  \api\v1\Service\Order\InsertUpdateOrderProduct::class);
  $app->post('/orders/{id}/products_attributes',             \api\v1\Service\Order\InsertOrderProductAttributes::class);
  $app->post('/orders/{id}/products_attributes/{aid}',       \api\v1\Service\Order\InsertUpdateOrderProductAttributes::class);
  $app->post('/orders/{id}/products_download',               \api\v1\Service\Order\InsertOrderProductDownload::class);
  $app->post('/orders/{id}/products_download/{did}',         \api\v1\Service\Order\InsertUpdateOrderProductDownload::class);
  $app->post('/orders/{id}/status_history',                  \api\v1\Service\Order\InsertOrderStatusHistory::class);
  $app->post('/orders/{id}/status_history/{hid}',            \api\v1\Service\Order\InsertUpdateOrderStatusHistory::class);
  $app->post('/orders/{id}/total',                           \api\v1\Service\Order\InsertOrderTotal::class);
  $app->post('/orders/{id}/total/{tid}',                     \api\v1\Service\Order\InsertUpdateOrderTotal::class);
  $app->post('/orders/{id}/tracking',                        \api\v1\Service\Order\InsertOrderTracking::class);
  $app->post('/orders/{id}/tracking/{tid}',                  \api\v1\Service\Order\InsertUpdateOrderTracking::class);

  // update orders
  $app->put('/orders/{id}',                                  \api\v1\Service\Order\InsertUpdateOrder::class);
  $app->put('/orders/{id}/products/{pid}',                   \api\v1\Service\Order\InsertUpdateOrderProduct::class);
  $app->put('/orders/{id}/products_attributes/{aid}',        \api\v1\Service\Order\InsertUpdateOrderProductAttributes::class);
  $app->put('/orders/{id}/products_download/{did}',          \api\v1\Service\Order\InsertUpdateOrderProductDownload::class);
  $app->put('/orders/{id}/status_history/{hid}',             \api\v1\Service\Order\InsertUpdateOrderStatusHistory::class);
  $app->put('/orders/{id}/total/{tid}',                      \api\v1\Service\Order\InsertUpdateOrderTotal::class);
  $app->put('/orders/{id}/tracking/{tid}',                   \api\v1\Service\Order\InsertUpdateOrderTracking::class);

  // delete orders
  $app->delete('/orders/{id}',                               \api\v1\Service\Order\DeleteOrder::class);
  $app->delete('/orders/{id}/products',                      \api\v1\Service\Order\DeleteAllProduct::class);
  $app->delete('/orders/{id}/products/{pid}',                \api\v1\Service\Order\DeleteProduct::class);
  $app->delete('/orders/{id}/products_attributes/{aid}',     \api\v1\Service\Order\DeleteProductAttributes::class);
  $app->delete('/orders/{id}/products_download/{did}',       \api\v1\Service\Order\DeleteProductDownload::class);
  $app->delete('/orders/{id}/status_history',                \api\v1\Service\Order\DeleteAllStatusHistory::class);
  $app->delete('/orders/{id}/status_history/{hid}',          \api\v1\Service\Order\DeleteStatusHistory::class);
  $app->delete('/orders/{id}/total',                         \api\v1\Service\Order\DeleteAllTotal::class);
  $app->delete('/orders/{id}/total/{tid}',                   \api\v1\Service\Order\DeleteTotal::class);
  $app->delete('/orders/{id}/tracking',                      \api\v1\Service\Order\DeleteAllTracking::class);
  $app->delete('/orders/{id}/tracking/{tid}',                \api\v1\Service\Order\DeleteTracking::class);
