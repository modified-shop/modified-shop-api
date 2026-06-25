<?php

/**
 * /includes/external/api/v1/config/attributes.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// options
$app->get('/attributes/options', \api\v1\Service\Attributes\GetOptions::class);
$app->get('/attributes/options/{id}', \api\v1\Service\Attributes\GetSingleOption::class);

// insert options
$app->post('/attributes/options', \api\v1\Service\Attributes\InsertOption::class);
$app->post('/attributes/options/{id}', \api\v1\Service\Attributes\InsertUpdateOption::class);

// update options
$app->put('/attributes/options/{id}', \api\v1\Service\Attributes\InsertUpdateOption::class);

// delete options
$app->delete('/attributes/options/{id}', \api\v1\Service\Attributes\DeleteOption::class);

// values
$app->get('/attributes/values', \api\v1\Service\Attributes\GetValues::class);
$app->get('/attributes/values/{id}', \api\v1\Service\Attributes\GetSingleValue::class);

// insert values
$app->post('/attributes/values', \api\v1\Service\Attributes\InsertValue::class);
$app->post('/attributes/values/{id}', \api\v1\Service\Attributes\InsertUpdateValue::class);

// update values
$app->put('/attributes/values/{id}', \api\v1\Service\Attributes\InsertUpdateValue::class);

// delete values
$app->delete('/attributes/values/{id}', \api\v1\Service\Attributes\DeleteValue::class);

// attributes
$app->get('/attributes/{id}', \api\v1\Service\Attributes\GetAttributes::class);

// insert attributes
$app->post('/attributes/{id}', \api\v1\Service\Attributes\InsertAttributes::class);

// delete attributes
$app->delete('/attributes/{id}/values', \api\v1\Service\Attributes\DeleteAllAttributes::class);
$app->delete('/attributes/{id}/values/{vid}', \api\v1\Service\Attributes\DeleteAttributes::class);
