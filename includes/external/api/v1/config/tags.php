<?php

/**
 * /includes/external/api/v1/config/tags.php
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
  $app->get('/tags/options',                    \api\v1\Service\Tags\GetOptions::class);
  $app->get('/tags/options/{id}',               \api\v1\Service\Tags\GetSingleOption::class);

  // insert options
  $app->post('/tags/options',                   \api\v1\Service\Tags\InsertOption::class);
  $app->post('/tags/options/{id}',              \api\v1\Service\Tags\InsertUpdateOption::class);

  // update options
  $app->put('/tags/options/{id}',               \api\v1\Service\Tags\InsertUpdateOption::class);

  // delete Tags
  $app->delete('/tags/options/{id}',            \api\v1\Service\Tags\DeleteOption::class);


  // values
  $app->get('/tags/values',                     \api\v1\Service\Tags\GetValues::class);
  $app->get('/tags/values/{id}',                \api\v1\Service\Tags\GetSingleValue::class);

  // insert values
  $app->post('/tags/values/{oid}',              \api\v1\Service\Tags\InsertValue::class);
  $app->post('/tags/values/{oid}/{id}',         \api\v1\Service\Tags\InsertUpdateValue::class);

  // update values
  $app->put('/tags/values/{oid}/{id}',          \api\v1\Service\Tags\InsertUpdateValue::class);

  // delete values
  $app->delete('/tags/values/{id}',             \api\v1\Service\Tags\DeleteValue::class);
