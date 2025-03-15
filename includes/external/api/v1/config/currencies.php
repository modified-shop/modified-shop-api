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
  $app->get('/currencies',                       \api\v1\Service\Currency\GetCurrencies::class);
  $app->get('/currencies/{id}',                  \api\v1\Service\Currency\GetSingleCurrency::class);

  // delete campaigns
  $app->delete('/currencies/{id}',               \api\v1\Service\Currency\DeleteCurrency::class);
