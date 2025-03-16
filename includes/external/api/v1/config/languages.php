<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // languages
  $app->get('/languages',                       \api\v1\Service\Language\GetLanguages::class);
  $app->get('/languages/{id}',                  \api\v1\Service\Language\GetSingleLanguage::class);

  // insert languages
  $app->post('/languages',                      \api\v1\Service\Language\InsertLanguage::class);
  $app->post('/languages/{id}',                 \api\v1\Service\Language\InsertUpdateLanguage::class);

  // update languages
  $app->put('/languages/{id}',                  \api\v1\Service\Language\InsertUpdateLanguage::class);

  // delete languages
  $app->delete('/languages/{id}',               \api\v1\Service\Language\DeleteLanguage::class);
