<?php

/**
 * /includes/external/api/v1/config/bootstrap.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

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
