<?php

/**
 * /includes/external/api/v1/config/currencies.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// currencies
$app->get('/currencies', \api\v1\Service\Currency\GetCurrencies::class);
$app->get('/currencies/{id}', \api\v1\Service\Currency\GetSingleCurrency::class);

// insert currencies
$app->post('/currencies', \api\v1\Service\Currency\InsertCurrency::class);
$app->post('/currencies/{id}', \api\v1\Service\Currency\InsertUpdateCurrency::class);

// update currencies
$app->put('/currencies/{id}', \api\v1\Service\Currency\InsertUpdateCurrency::class);

// delete currencies
$app->delete('/currencies/{id}', \api\v1\Service\Currency\DeleteCurrency::class);
