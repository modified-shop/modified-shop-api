<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  use DI\ContainerBuilder;
  use Slim\App;

  require_once(DIR_FS_EXTERNAL.'Slim/autoload.php');

  $containerBuilder = new ContainerBuilder();

  // Add DI container definitions
  $containerBuilder->addDefinitions(__DIR__ . '/container.php');

  // Create DI container instance
  $container = $containerBuilder->build();

  // Create Slim App instance
  $app = $container->get(App::class);
  
  // Register routes
  (require __DIR__ . '/routes.php')($app);

  // Register middleware
  (require __DIR__ . '/middleware.php')($app);

  return $app;
