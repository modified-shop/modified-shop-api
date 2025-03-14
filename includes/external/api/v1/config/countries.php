<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // customers
  $app->get('/countries',                           \api\v1\Service\Customer\GetCountries::class);
  $app->get('/countries/{id}',                      \api\v1\Service\Customer\GetSingleCountry::class);
  
  $app->get('/countries/geo_zones',                 \api\v1\Service\Customer\GetGeoZones::class);
  $app->get('/countries/geo_zones/{id}',            \api\v1\Service\Customer\GetSingleGeoZone::class);

  $app->get('/countries/tax_class',                 \api\v1\Service\Customer\GetTaxClass::class);
  $app->get('/countries/tax_class/{id}',            \api\v1\Service\Customer\GetSingleTaxClass::class);

  $app->get('/countries/tax_rates',                 \api\v1\Service\Customer\GetTaxRates::class);
  $app->get('/countries/tax_rates/{id}',            \api\v1\Service\Customer\GetSingleTaxRate::class);

  $app->get('/countries/zones',                     \api\v1\Service\Customer\GetZones::class);
  $app->get('/countries/zones/{id}',                \api\v1\Service\Customer\GetSingleZone::class);

  $app->get('/countries/zones_to_geo_zones',        \api\v1\Service\Customer\GetZonesToGeoZones::class);
  $app->get('/countries/zones_to_geo_zones/{id}',   \api\v1\Service\Customer\GetSingleZonesToGeoZone::class);
