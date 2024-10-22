<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // Timezone
  date_default_timezone_set('Europe/Berlin');

  // Settings
  $settings = [];

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
      'secret' => MODULE_SYSTEM_MODIFIED_API_SECRET,
      'algorithm' => 'HS256',
      'secure' => true,
  ];

  // Error reporting
  error_reporting($settings['error']['display_error_details'] === true ? -1 : 0);
  ini_set('display_errors', $settings['error']['display_error_details']);

  return $settings;