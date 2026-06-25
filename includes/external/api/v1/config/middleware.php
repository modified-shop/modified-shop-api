<?php

/**
 * /includes/external/api/v1/config/middleware.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

use Slim\App;
  use Slim\Middleware\ErrorMiddleware;
  use Selective\BasePath\BasePathMiddleware;
  use Selective\Validation\Middleware\ValidationExceptionMiddleware;

  return function (App $app) {
      $app->addBodyParsingMiddleware();
      $app->add(ValidationExceptionMiddleware::class);
      $app->addRoutingMiddleware();
      $app->add(BasePathMiddleware::class);
      $app->add(ErrorMiddleware::class);
  };