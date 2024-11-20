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

  // delete attributes
  $app->delete('/attributes/options/{id}',            \api\v1\Service\Attributes\DeleteOption::class);

  // values
  $app->get('/attributes/values',                     \api\v1\Service\Attributes\GetValues::class);
  $app->get('/attributes/values/{id}',                \api\v1\Service\Attributes\GetSingleValue::class);

  // delete attributes
  $app->delete('/attributes/values/{id}',             \api\v1\Service\Attributes\DeleteValue::class);

  // attributes
  $app->get('/attributes/{id}',                       \api\v1\Service\Attributes\GetAttributes::class);

  // delete attributes
  $app->delete('/attributes/{id}/values',             \api\v1\Service\Attributes\DeleteAllAttributes::class);
  $app->delete('/attributes/{id}/values/{vid}',       \api\v1\Service\Attributes\DeleteAttributes::class);
