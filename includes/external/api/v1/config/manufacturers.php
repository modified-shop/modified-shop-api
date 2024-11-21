<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // manufacturers
  $app->get('/manufacturers',                            \api\v1\Service\Manufacturer\GetManufacturers::class);
  $app->get('/manufacturers/{id}',                       \api\v1\Service\Manufacturer\GetSingleManufacturer::class);
  $app->get('/manufacturers/{id}/description',           \api\v1\Service\Manufacturer\GetManufacturerDescription::class);
  $app->get('/manufacturers/{id}/products',              \api\v1\Service\Manufacturer\GetManufacturerProducts::class);
  
  // insert manufacturers
  $app->post('/manufacturers',                           \api\v1\Service\Manufacturer\InsertManufacturer::class);
  $app->post('/manufacturers/manufacturers',             \api\v1\Service\Manufacturer\InsertUpdateManufacturer::class);
  $app->post('/manufacturers/{id}/description',          \api\v1\Service\Manufacturer\InsertUpdateDescription::class);
  $app->post('/manufacturers/{id}/products',             \api\v1\Service\Manufacturer\InsertUpdateProducts::class);
  $app->post('/manufacturers/{id}/image',                \api\v1\Service\Manufacturer\InsertUpdateImage::class);

  // update manufacturers
  $app->put('/manufacturers/{id}',                       \api\v1\Service\Manufacturer\UpdateManufacturer::class);
  $app->put('/manufacturers/{id}/manufacturers',         \api\v1\Service\Manufacturer\InsertUpdateManufacturer::class);
  $app->put('/manufacturers/{id}/description',           \api\v1\Service\Manufacturer\InsertUpdateDescription::class);
  $app->put('/manufacturers/{id}/products',              \api\v1\Service\Manufacturer\InsertUpdateProducts::class);

  // delete manufacturers
  $app->delete('/manufacturers/{id}',                    \api\v1\Service\Manufacturer\DeleteManufacturer::class);
  $app->delete('/manufacturers/{id}/products',           \api\v1\Service\Manufacturer\DeleteAllProducts::class);
  $app->delete('/manufacturers/{id}/products/{pid}',     \api\v1\Service\Manufacturer\DeleteProduct::class);
  $app->delete('/manufacturers/{id}/image',              \api\v1\Service\Manufacturer\DeleteImage::class);
