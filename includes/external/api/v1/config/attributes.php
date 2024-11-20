<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // options
  $app->get('/attributes/options',                    \api\v1\Service\Attributes\GetOptions::class);
  $app->get('/attributes/options/{id}',               \api\v1\Service\Attributes\GetSingleOption::class);

  // values
  $app->get('/attributes/values',                     \api\v1\Service\Attributes\GetValues::class);
  $app->get('/attributes/values/{id}',                \api\v1\Service\Attributes\GetSingleValue::class);

  // attributes
  $app->get('/attributes/{id}',            \api\v1\Service\Attributes\GetAttributes::class);
