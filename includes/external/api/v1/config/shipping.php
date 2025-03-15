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
  $app->get('/shipping/carrier',                   \api\v1\Service\Shipping\GetCarrier::class);
  $app->get('/shipping/status',                    \api\v1\Service\Shipping\GetShippingStatus::class);

  $app->get('/shipping/carrier/{id}',              \api\v1\Service\Shipping\GetSingleCarrier::class);
  $app->get('/shipping/status/{id}',               \api\v1\Service\Shipping\GetSingleShippingStatus::class);
