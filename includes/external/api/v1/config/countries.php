<?php

/**
 * /includes/external/api/v1/config/countries.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// countries
$app->get('/countries', \api\v1\Service\Country\GetCountries::class);
$app->get('/countries/geo_zones', \api\v1\Service\Country\GetGeoZones::class);
$app->get('/countries/tax_class', \api\v1\Service\Country\GetTaxClass::class);
$app->get('/countries/tax_rates', \api\v1\Service\Country\GetTaxRates::class);

$app->get('/countries/{id}', \api\v1\Service\Country\GetSingleCountry::class);
$app->get('/countries/{id}/zones', \api\v1\Service\Country\GetZones::class);
$app->get('/countries/geo_zones/{id}', \api\v1\Service\Country\GetSingleGeoZone::class);
$app->get('/countries/tax_class/{id}', \api\v1\Service\Country\GetSingleTaxClass::class);
$app->get('/countries/tax_rates/{id}', \api\v1\Service\Country\GetSingleTaxRate::class);
