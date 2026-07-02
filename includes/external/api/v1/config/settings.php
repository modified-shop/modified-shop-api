<?php

/**
 * /includes/external/api/v1/config/settings.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// Timezone
date_default_timezone_set('Europe/Berlin');

// Settings
$settings = [];

// API version and requirements (single source of truth)
$settings['version'] = '1.0.0';
$settings['min_shop_version'] = '3.2.0';

// Path settings
$settings['root'] = dirname(__DIR__);
$settings['public'] = $settings['root'];

// Error Handling Middleware settings
$settings['error'] = [
    // Should be set to false in production
    'display_error_details' => false,
    // Should be set to false for unit tests
    'log_errors' => true,
    // Display error details in error log
    'log_error_details' => true,
];

// Logger settings
$settings['logger'] = [
    'name' => 'API',
    'path' => DIR_FS_LOG,
    'filename' => 'mod_api_%s_%s.log',
    'level' => 'debug',
    'file_permission' => 0775,
];

// JWT
$settings['jwt'] = [
    'secret' => ((defined('MODULE_API_ACCESS_SECRET')) ? MODULE_API_ACCESS_SECRET : 'supersecretkeyyoushouldnottellanyone'),
    'algorithm' => 'HS256',
    'secure' => true,
];

// Error reporting
error_reporting($settings['error']['display_error_details'] === true ? -1 : 0);
ini_set('display_errors', $settings['error']['display_error_details']);

return $settings;
