<?php

/**
 * /includes/external/api/v1/config/languages.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// languages
$app->get('/languages', \api\v1\Service\Language\GetLanguages::class);
$app->get('/languages/{id}', \api\v1\Service\Language\GetSingleLanguage::class);

// insert languages
$app->post('/languages', \api\v1\Service\Language\InsertLanguage::class);
$app->post('/languages/{id}', \api\v1\Service\Language\InsertUpdateLanguage::class);

// update languages
$app->put('/languages/{id}', \api\v1\Service\Language\InsertUpdateLanguage::class);

// delete languages
$app->delete('/languages/{id}', \api\v1\Service\Language\DeleteLanguage::class);
