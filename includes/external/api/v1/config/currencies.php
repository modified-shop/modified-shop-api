<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // currencies
  $app->get('/currencies',                       \api\v1\Service\Currency\GetCurrencies::class);
  $app->get('/currencies/{id}',                  \api\v1\Service\Currency\GetSingleCurrency::class);

  // insert currencies
  $app->post('/currencies',                      \api\v1\Service\Currency\InsertCurrency::class);
  $app->post('/currencies/{id}',                  \api\v1\Service\Currency\InsertUpdateCurrency::class);

  // update currencies
  $app->put('/currencies/{id}',                  \api\v1\Service\Currency\InsertUpdateCurrency::class);

  // delete currencies
  $app->delete('/currencies/{id}',               \api\v1\Service\Currency\DeleteCurrency::class);
