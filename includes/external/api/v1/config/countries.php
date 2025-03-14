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
  $app->get('/countries',                           \api\v1\Service\Country\GetCountries::class);
  $app->get('/countries/{id}',                      \api\v1\Service\Country\GetSingleCountry::class);
  
  $app->get('/countries/geo_zones',                 \api\v1\Service\Country\GetGeoZones::class);
  $app->get('/countries/geo_zones/{id}',            \api\v1\Service\Country\GetSingleGeoZone::class);

  $app->get('/countries/tax_class',                 \api\v1\Service\Country\GetTaxClass::class);
  $app->get('/countries/tax_class/{id}',            \api\v1\Service\Country\GetSingleTaxClass::class);

  $app->get('/countries/tax_rates',                 \api\v1\Service\Country\GetTaxRates::class);
  $app->get('/countries/tax_rates/{id}',            \api\v1\Service\Country\GetSingleTaxRate::class);

  $app->get('/countries/zones',                     \api\v1\Service\Country\GetZones::class);
  $app->get('/countries/zones/{id}',                \api\v1\Service\Country\GetSingleZone::class);

  $app->get('/countries/zones_to_geo_zones',        \api\v1\Service\Country\GetZonesToGeoZones::class);
  $app->get('/countries/zones_to_geo_zones/{id}',   \api\v1\Service\Country\GetSingleZonesToGeoZone::class);
