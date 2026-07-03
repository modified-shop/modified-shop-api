<?php

/**
 * /admin/includes/modules/system/api_access.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// include needed functions
require_once(DIR_FS_INC . 'xtc_rand.inc.php');

class api_access
{
    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $properties;
    var $_check;

    function __construct()
    {
        $this->code = 'api_access';
        $this->title = MODULE_API_ACCESS_TEXT_TITLE;
        $this->description = MODULE_API_ACCESS_TEXT_DESCRIPTION;
        $this->sort_order = ((defined('MODULE_API_ACCESS_SORT_ORDER')) ? MODULE_API_ACCESS_SORT_ORDER : '');
        $this->enabled = ((defined('MODULE_API_ACCESS_STATUS') && MODULE_API_ACCESS_STATUS == 'true') ? true : false);

        $this->properties['button_update'] = '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code . '&action=update') . '">' . BUTTON_UPDATE . '</a>';
    }

    function process($file)
    {
      //do nothing
    }

    function check()
    {
        if (!isset($this->_check)) {
            if (defined('MODULE_API_ACCESS_STATUS')) {
                $this->_check = true;
            } else {
                $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM " . TABLE_CONFIGURATION . " 
                                        WHERE configuration_key = 'MODULE_API_ACCESS_STATUS'");
                $this->_check = xtc_db_num_rows($check_query);
            }
        }
        return $this->_check;
    }

    function update()
    {
        global $messageStack;

        // Customer
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Customer/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 10 WHERE customers_id = 'groups'");
            }
        }

        // Category
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Category/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 20 WHERE customers_id = 'groups'");
            }
        }

        // Product
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Product/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 30 WHERE customers_id = 'groups'");
            }
        }

        // Manufacturer
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Manufacturer/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 31 WHERE customers_id = 'groups'");
            }
        }

        // Attributes
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Attributes/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 32 WHERE customers_id = 'groups'");
            }
        }

        // Tags
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Tags/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 33 WHERE customers_id = 'groups'");
            }
        }

        // Order
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Order/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 40 WHERE customers_id = 'groups'");
            }
        }

        // Country
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Country/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 50 WHERE customers_id = 'groups'");
            }
        }

        // Shipping
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Shipping/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 60 WHERE customers_id = 'groups'");
            }
        }

        // Campaign
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Campaign/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 70 WHERE customers_id = 'groups'");
            }
        }

        // Currency
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Currency/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 80 WHERE customers_id = 'groups'");
            }
        }

        // Language
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Language/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 90 WHERE customers_id = 'groups'");
            }
        }

        // Newsletter
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Newsletter/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 100 WHERE customers_id = 'groups'");
            }
        }

        // Configuration
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Configuration/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 110 WHERE customers_id = 'groups'");
            }
        }

        // Content
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Content/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 120 WHERE customers_id = 'groups'");
            }
        }

        // Coupon
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Coupon/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 130 WHERE customers_id = 'groups'");
            }
        }

        // Schema
        $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/Schema/');
        foreach ($column_array as $column) {
            $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($column) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                xtc_db_query("ALTER TABLE `api_access` ADD " . $column . " int(1) NOT NULL DEFAULT '0'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 1  WHERE customers_id = '1'");
                xtc_db_query("UPDATE `api_access` SET " . $column . " = 140 WHERE customers_id = 'groups'");
            }
        }
    }

    function get_dir_content($filedir)
    {
        $files = array();
        if ($dir = opendir($filedir)) {
            while ($file = readdir($dir)) {
                $ext = substr($file, strrpos($file, '.') + 1);
                if (is_file($filedir . $file) && $ext == 'php') {
                    $files[] = substr($file, 0, strrpos($file, '.'));
                }
            }
            closedir($dir);
        }

        return $files;
    }

    function display()
    {
        return array('text' => '<br />' .
                             '<br />' .
                             xtc_button(BUTTON_SAVE) .
                             xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code))
                  );
    }

    function install()
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_ACCESS_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_ACCESS_SECRET', '" . md5(time() . xtc_rand(0, 99999)) . "',  '6', '1', '', now())");

        xtc_db_query("CREATE TABLE IF NOT EXISTS `api_access` (
                      `customers_id` varchar(32) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`customers_id`)
                    )");

        xtc_db_query("INSERT INTO api_access (customers_id) VALUES (1)");
        xtc_db_query("INSERT INTO api_access (customers_id) VALUES ('groups')");

        xtc_db_query("CREATE TABLE IF NOT EXISTS `api_refresh_tokens` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `customers_id` int(11) NOT NULL,
                      `token_hash` char(64) NOT NULL,
                      `expires_at` int(11) NOT NULL,
                      `created_at` int(11) NOT NULL,
                      `revoked` tinyint(1) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `token_hash` (`token_hash`),
                      KEY `customers_id` (`customers_id`)
                    )");
 
        $query_result = xtc_db_query("SHOW COLUMNS FROM `" . TABLE_ADMIN_ACCESS . "`");
        $db_table_rows = array();
        while ($row = xtc_db_fetch_array($query_result)) {
            $db_table_rows[] = $row['Field'];
        }

        if (!in_array('api_access', $db_table_rows)) {
            xtc_db_query("ALTER TABLE `" . TABLE_ADMIN_ACCESS . "` ADD `api_access` INT(1) NOT NULL DEFAULT 0");
            xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `api_access` = 1 WHERE `customers_id` = 1");
            xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `api_access` = 1 WHERE `customers_id` = " . $_SESSION['customer_id']);
            xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `api_access` = 2 WHERE `customers_id` = 'groups'");
        }

        $this->update();
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_API_ACCESS_%'");
    }

    // keys
    function keys()
    {
        return array(
        'MODULE_API_ACCESS_STATUS',
        );
    }
}
