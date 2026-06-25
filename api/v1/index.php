<?php

/**
 * /api/v1/index.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

include(dirname(__FILE__) . '/../../includes/application_top_callback.php');

// needed constants
define('DIR_FS_ADMIN', DIR_FS_CATALOG . DIR_ADMIN);

// file upload
defined('ERROR_DESTINATION_DOES_NOT_EXIST')   or define('ERROR_DESTINATION_DOES_NOT_EXIST', 'Error: Destination does not exist.');
defined('ERROR_DESTINATION_NOT_WRITEABLE')    or define('ERROR_DESTINATION_NOT_WRITEABLE', 'Error: Destination is not writeable.');
defined('ERROR_FILE_NOT_SAVED')               or define('ERROR_FILE_NOT_SAVED', 'Error: File upload not saved.');
defined('ERROR_FILETYPE_NOT_ALLOWED')         or define('ERROR_FILETYPE_NOT_ALLOWED', 'Error: File upload type not allowed.');
defined('SUCCESS_FILE_SAVED_SUCCESSFULLY')    or define('SUCCESS_FILE_SAVED_SUCCESSFULLY', 'Success: File upload saved successfully.');
defined('WARNING_NO_FILE_UPLOADED')           or define('WARNING_NO_FILE_UPLOADED', 'Warnung: No file uploaded.');
defined('ERROR_FILE_NOT_REMOVEABLE')          or define('ERROR_FILE_NOT_REMOVEABLE', 'Error: File not removed.');

(require(DIR_FS_EXTERNAL . 'api/v1/config/bootstrap.php'))->run();
