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

require_once(DIR_FS_EXTERNAL . 'Slim/autoload.php');

$containerBuilder = new ContainerBuilder();

// Add DI container definitions
$containerBuilder->addDefinitions(__DIR__ . '/container.php');

// Create DI container instance
$container = $containerBuilder->build();

// Application settings (single source of truth for version + requirements)
$settings = $container->get('settings');

// The running shop version (PROJECT_VERSION_NO) is not loaded by
// application_top, so pull it in from the shop core when available.
if (
    !defined('PROJECT_VERSION_NO')
    && defined('DIR_FS_ADMIN')
    && is_file(DIR_FS_ADMIN . 'includes/version.php')
) {
    require_once(DIR_FS_ADMIN . 'includes/version.php');
}

// Enforce the minimum shop version.
if (
    defined('PROJECT_VERSION_NO')
    && version_compare(PROJECT_VERSION_NO, $settings['min_shop_version'], '<')
) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo (string)json_encode([
        'error' => sprintf(
            'modified shop %s or higher required',
            $settings['min_shop_version']
        ),
    ]);
    exit;
}

// Create Slim App instance
$app = $container->get(App::class);

// Register routes
(require __DIR__ . '/routes.php')($app);

// Register middleware
(require __DIR__ . '/middleware.php')($app);

return $app;
