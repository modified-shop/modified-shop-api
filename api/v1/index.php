<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  include (dirname(__FILE__).'/../../includes/application_top_callback.php');
  
  defined('MODULE_SYSTEM_MODIFIED_API_SECRET') OR define('MODULE_SYSTEM_MODIFIED_API_SECRET', 'supersecretkeyyoushouldnotcommittogithub');
  defined('MODULE_SYSTEM_MODIFIED_API_USER') OR define('MODULE_SYSTEM_MODIFIED_API_USER', 'admin');
  defined('MODULE_SYSTEM_MODIFIED_API_PASS') OR define('MODULE_SYSTEM_MODIFIED_API_PASS', 'admin');
  
  (require (DIR_FS_EXTERNAL . 'api/v1/config/bootstrap.php'))->run();
