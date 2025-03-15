<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // campaigns
  $app->get('/currencies',                       \api\v1\Service\Campaign\GetCurrencies::class);
  $app->get('/currencies/{id}',                  \api\v1\Service\Campaign\GetSingleCurrency::class);
