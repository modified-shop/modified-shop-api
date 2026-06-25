<?php

/**
 * /includes/external/api/v1/config/categories.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// categories
$app->get('/categories', \api\v1\Service\Category\GetCategories::class);
$app->get('/categories/{id}', \api\v1\Service\Category\GetSingleCategory::class);
$app->get('/categories/{id}/categories', \api\v1\Service\Category\GetCategory::class);
$app->get('/categories/{id}/description', \api\v1\Service\Category\GetCategoryDescription::class);
$app->get('/categories/{id}/products', \api\v1\Service\Category\GetCategoryProducts::class);

// insert categories
$app->post('/categories', \api\v1\Service\Category\InsertCategory::class);
$app->post('/categories/{id}/categories', \api\v1\Service\Category\InsertUpdateCategory::class);
$app->post('/categories/{id}/description', \api\v1\Service\Category\InsertUpdateDescription::class);
$app->post('/categories/{id}/products', \api\v1\Service\Category\InsertUpdateProducts::class);
$app->post('/categories/{id}/images', \api\v1\Service\Category\InsertUpdateImages::class);

// update categories
$app->put('/categories/{id}', \api\v1\Service\Category\UpdateCategory::class);
$app->put('/categories/{id}/categories', \api\v1\Service\Category\InsertUpdateCategory::class);
$app->put('/categories/{id}/description', \api\v1\Service\Category\InsertUpdateDescription::class);
$app->put('/categories/{id}/products', \api\v1\Service\Category\InsertUpdateProducts::class);

// delete categories
$app->delete('/categories/{id}', \api\v1\Service\Category\DeleteCategory::class);
$app->delete('/categories/{id}/products', \api\v1\Service\Category\DeleteAllProducts::class);
$app->delete('/categories/{id}/products/{pid}', \api\v1\Service\Category\DeleteProduct::class);
$app->delete('/categories/{id}/images', \api\v1\Service\Category\DeleteImages::class);
