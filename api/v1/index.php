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
  
  defined('MODULE_SYSTEM_MODIFIED_API_SECRET')  OR define('MODULE_SYSTEM_MODIFIED_API_SECRET', 'supersecretkeyyoushouldnotcommittogithub');
  defined('MODULE_SYSTEM_MODIFIED_API_USER')    OR define('MODULE_SYSTEM_MODIFIED_API_USER', 'admin');
  defined('MODULE_SYSTEM_MODIFIED_API_PASS')    OR define('MODULE_SYSTEM_MODIFIED_API_PASS', 'admin');
  
  // file upload
  defined('ERROR_DESTINATION_DOES_NOT_EXIST')   OR define('ERROR_DESTINATION_DOES_NOT_EXIST', 'Error: Destination does not exist.');
  defined('ERROR_DESTINATION_NOT_WRITEABLE')    OR define('ERROR_DESTINATION_NOT_WRITEABLE', 'Error: Destination is not writeable.');
  defined('ERROR_FILE_NOT_SAVED')               OR define('ERROR_FILE_NOT_SAVED', 'Error: File upload not saved.');
  defined('ERROR_FILETYPE_NOT_ALLOWED')         OR define('ERROR_FILETYPE_NOT_ALLOWED', 'Error: File upload type not allowed.');
  defined('SUCCESS_FILE_SAVED_SUCCESSFULLY')    OR define('SUCCESS_FILE_SAVED_SUCCESSFULLY', 'Success: File upload saved successfully.');
  defined('WARNING_NO_FILE_UPLOADED')           OR define('WARNING_NO_FILE_UPLOADED', 'Warnung: No file uploaded.');
  defined('ERROR_FILE_NOT_REMOVEABLE')          OR define('ERROR_FILE_NOT_REMOVEABLE', 'Error: File not removed.');
  
  (require (DIR_FS_EXTERNAL . 'api/v1/config/bootstrap.php'))->run();
