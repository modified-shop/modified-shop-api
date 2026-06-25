<?php

/**
 * /includes/external/api/v1/config/schema.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// schema
  $app->get('/schema/{table}',                  \api\v1\Service\Schema\GetSchema::class);
