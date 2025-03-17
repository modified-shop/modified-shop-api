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
  use Slim\Routing\RouteCollectorProxy;
  use Tuupola\Middleware\HttpBasicAuthentication;
  use Tuupola\Middleware\JwtAuthentication;
  use api\v1\Auth\Authentication;

  return function (App $app) {
      $app->post('/v1/oauth', \api\v1\Auth\JwtAuth::class)->add(Authentication::class);
      
      // order
      $app->group(
          '/v1',
          function (RouteCollectorProxy $app) {
          
              // customers
              include __DIR__ . '/customers.php';
                            
              // categories
              include __DIR__ . '/categories.php';

              // products
              include __DIR__ . '/products.php';

              // manufacturers
              include __DIR__ . '/manufacturers.php';

              // attributes
              include __DIR__ . '/attributes.php';

              // tags
              include __DIR__ . '/tags.php';
              
              // orders
              include __DIR__ . '/orders.php';

              // countries
              include __DIR__ . '/countries.php';

              // shipping
              include __DIR__ . '/shipping.php';

              // contents
              include __DIR__ . '/contents.php';
              
              // campaigns
              include __DIR__ . '/campaigns.php';

              // currencies
              include __DIR__ . '/currencies.php';

              // languages
              include __DIR__ . '/languages.php';

              // newsletters
              include __DIR__ . '/newsletters.php';

              // configurations
              include __DIR__ . '/configurations.php';

              // dhl
              include __DIR__ . '/dhl.php';

          }
      )->add(JwtAuthentication::class);
  };
