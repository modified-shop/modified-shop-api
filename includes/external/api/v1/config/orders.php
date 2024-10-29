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
  $app->get('/orders',                        \api\v1\Service\Order\GetAllOrders::class);
  $app->get('/orders/{id}',                   \api\v1\Service\Order\GetSingleOrder::class);
  
  // update orders
  $app->post('/orders/status/{id}',           \api\v1\Service\Order\UpdateOrderStatus::class);
  $app->post('/orders/tracking/{id}',         \api\v1\Service\Order\InsertOrderTracking::class);

  // delete orders
  $app->delete('/orders/{id}',                \api\v1\Service\Order\DeleteOrder::class);
