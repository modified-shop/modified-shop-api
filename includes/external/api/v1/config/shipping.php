<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // shipping
  $app->get('/shipping/carriers',                  \api\v1\Service\Shipping\GetCarrier::class);
  $app->get('/shipping/status',                    \api\v1\Service\Shipping\GetShippingStatus::class);

  $app->get('/shipping/carriers/{id}',             \api\v1\Service\Shipping\GetSingleCarrier::class);
  $app->get('/shipping/status/{id}',               \api\v1\Service\Shipping\GetSingleShippingStatus::class);

  // insert shipping
  $app->post('/shipping/carriers',                 \api\v1\Service\Shipping\InsertCarrier::class);
  $app->post('/shipping/status',                   \api\v1\Service\Shipping\InsertShippingStatus::class);
  $app->post('/shipping/carriers/{id}',            \api\v1\Service\Shipping\InsertUpdateCarrier::class);
  $app->post('/shipping/status/{id}',              \api\v1\Service\Shipping\InsertUpdateShippingStatus::class);

  // update shipping
  $app->put('/shipping/carriers/{id}',             \api\v1\Service\Shipping\InsertUpdateCarrier::class);
  $app->put('/shipping/status/{id}',               \api\v1\Service\Shipping\InsertUpdateShippingStatus::class);

  // delete shipping
  $app->delete('/shipping/carriers/{id}',          \api\v1\Service\Shipping\DeleteCarrier::class);
  $app->delete('/shipping/status/{id}',            \api\v1\Service\Shipping\DeleteShippingStatus::class);
