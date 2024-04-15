<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

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