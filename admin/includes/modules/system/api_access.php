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

class api_access
{
    public $code;
    public $title;
    public $description;
    public $sort_order;
    public $enabled;
    public $properties;
    public $_check;

    public function __construct()
    {
        $this->code = 'api_access';
        $this->title = MODULE_API_ACCESS_TEXT_TITLE;
        $this->description = MODULE_API_ACCESS_TEXT_DESCRIPTION;
        $this->sort_order = ((defined('MODULE_API_ACCESS_SORT_ORDER')) ? MODULE_API_ACCESS_SORT_ORDER : '');
        $this->enabled = ((defined('MODULE_API_ACCESS_STATUS') && MODULE_API_ACCESS_STATUS == 'true') ? true : false);

        $this->properties['button_update'] = '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code . '&action=update') . '">' . BUTTON_UPDATE . '</a>';
    }

    public function process($file)
    {
        //do nothing
    }

    public function check()
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

    public function update()
    {
        global $messageStack;

        // API access groups
        $resource_groups = array(
            array('name' => 'Customer',      'color' => '#eeeeee', 'sort_order' => 10),
            array('name' => 'Category',      'color' => '#ebbb97', 'sort_order' => 20),
            array('name' => 'Product',       'color' => '#aacfe2', 'sort_order' => 30),
            array('name' => 'Manufacturer',  'color' => '#ebd397', 'sort_order' => 31),
            array('name' => 'Attributes',    'color' => '#afd088', 'sort_order' => 32),
            array('name' => 'Tags',          'color' => '#d0af88', 'sort_order' => 33),
            array('name' => 'Order',         'color' => '#617d8d', 'sort_order' => 40),
            array('name' => 'Country',       'color' => '#666666', 'sort_order' => 50),
            array('name' => 'Shipping',      'color' => '#cb7272', 'sort_order' => 60),
            array('name' => 'Campaign',      'color' => '#8cd1ba', 'sort_order' => 70),
            array('name' => 'Currency',      'color' => '#c689ab', 'sort_order' => 80),
            array('name' => 'Language',      'color' => '#ffaaa5', 'sort_order' => 90),
            array('name' => 'Newsletter',    'color' => '#dcedc1', 'sort_order' => 100),
            array('name' => 'Configuration', 'color' => '#66545e', 'sort_order' => 110),
            array('name' => 'Content',       'color' => '#a39193', 'sort_order' => 120),
            array('name' => 'Coupon',        'color' => '#aa6f73', 'sort_order' => 130),
            array('name' => 'Schema',        'color' => '#ffecef', 'sort_order' => 140),
            array('name' => 'Dhl',           'color' => '#b8b8d1', 'sort_order' => 150),
            array('name' => 'Webhook',       'color' => '#9fb4c7', 'sort_order' => 160),
        );

        xtc_db_query("CREATE TABLE IF NOT EXISTS `api_access_groups` (
                      `group_id` int(11) NOT NULL AUTO_INCREMENT,
                      `resource_name` varchar(64) NOT NULL,
                      `color` varchar(7) NOT NULL,
                      `sort_order` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`group_id`),
                      UNIQUE KEY `idx_resource_name` (`resource_name`)
                    )");

        foreach ($resource_groups as $group_info) {
            $resource_name = $group_info['name'];

            $check_query = xtc_db_query("SELECT group_id
                                             FROM `api_access_groups`
                                            WHERE resource_name = '" . xtc_db_input($resource_name) . "'");
            if (xtc_db_num_rows($check_query) < 1) {
                $sql_data_array = array(
                    'resource_name' => $resource_name,
                    'color' => $group_info['color'],
                    'sort_order' => (int)$group_info['sort_order'],
                );
                xtc_db_perform('api_access_groups', $sql_data_array);
            }

            // Permission columns are qualified as `{ResourceName}{Action}`
            $column_array = $this->get_dir_content(DIR_FS_EXTERNAL . 'api/v1/Service/' . $resource_name . '/');
            foreach ($column_array as $column) {
                $qualified_column = $resource_name . $column;

                $check_query = xtc_db_query("SHOW COLUMNS FROM `api_access` LIKE '" . xtc_db_input($qualified_column) . "'");
                if (xtc_db_num_rows($check_query) < 1) {
                    xtc_db_query("ALTER TABLE `api_access` ADD `" . xtc_db_input($qualified_column) . "` int(1) NOT NULL DEFAULT '0'");
                    xtc_db_query("UPDATE `api_access` SET `" . xtc_db_input($qualified_column) . "` = 1 WHERE customers_id = '1'");
                }
            }
        }
    }

    public function get_dir_content($filedir)
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

    public function display()
    {
        return array('text' => '<br />' .
                             '<br />' .
                             xtc_button(BUTTON_SAVE) .
                             xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code))
        );
    }

    public function install()
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_ACCESS_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_ACCESS_SECRET', '" . bin2hex(random_bytes(32)) . "',  '6', '1', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_ACCESS_WEBHOOKS_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_ACCESS_WEBHOOKS_CRON_SECRET', '" . bin2hex(random_bytes(32)) . "',  '6', '1', '', now())");

        xtc_db_query("CREATE TABLE IF NOT EXISTS `api_access` (
                      `customers_id` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`customers_id`)
                    )");

        xtc_db_query("INSERT INTO api_access (customers_id) VALUES (1)");

        xtc_db_query("CREATE TABLE IF NOT EXISTS `api_refresh_tokens` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `customers_id` int(11) NOT NULL,
                      `token_hash` char(64) NOT NULL,
                      `expires_at` int(11) NOT NULL,
                      `created_at` int(11) NOT NULL,
                      `revoked` tinyint(1) NOT NULL DEFAULT '0',
                      `revoked_at` int(11) NOT NULL DEFAULT '0',
                      `device_id` varchar(191) NOT NULL DEFAULT '',
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `idx_token_hash` (`token_hash`),
                      KEY `idx_customers_id` (`customers_id`)
                    )");

        xtc_db_query("CREATE TABLE IF NOT EXISTS `api_rate_limit` (
                      `rl_key` varchar(191) NOT NULL,
                      `attempts` int(11) NOT NULL DEFAULT '0',
                      `window_start` int(11) NOT NULL DEFAULT '0',
                      `blocked_until` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`rl_key`)
                    )");

        xtc_db_query("CREATE TABLE IF NOT EXISTS `api_events` (
                      `event_id` int(11) NOT NULL AUTO_INCREMENT,
                      `event_type` varchar(64) NOT NULL,
                      `entity_id` int(11) NOT NULL DEFAULT '0',
                      `payload` text NOT NULL,
                      `fanned_out` tinyint(1) NOT NULL DEFAULT '0',
                      `created_at` int(11) NOT NULL,
                      PRIMARY KEY (`event_id`),
                      KEY `idx_fanned_out` (`fanned_out`),
                      KEY `idx_created_at` (`created_at`)
                    )");

        xtc_db_query("CREATE TABLE IF NOT EXISTS `api_subscriptions` (
                      `subscription_id` int(11) NOT NULL AUTO_INCREMENT,
                      `customers_id` int(11) NOT NULL,
                      `transport` varchar(16) NOT NULL DEFAULT 'webhook',
                      `url` varchar(2048) NOT NULL,
                      `label` varchar(64) NOT NULL DEFAULT '',
                      `secret` char(64) NOT NULL,
                      `event_types` text NOT NULL,
                      `active` tinyint(1) NOT NULL DEFAULT '1',
                      `consecutive_failures` int(11) NOT NULL DEFAULT '0',
                      `disabled_reason` varchar(255) NOT NULL DEFAULT '',
                      `last_success_at` int(11) NOT NULL DEFAULT '0',
                      `last_failure_at` int(11) NOT NULL DEFAULT '0',
                      `created_at` int(11) NOT NULL,
                      `updated_at` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`subscription_id`),
                      KEY `idx_customers_id` (`customers_id`),
                      KEY `idx_active` (`active`)
                    )");

        xtc_db_query("CREATE TABLE IF NOT EXISTS `api_event_deliveries` (
                      `delivery_id` int(11) NOT NULL AUTO_INCREMENT,
                      `event_id` int(11) NOT NULL,
                      `subscription_id` int(11) NOT NULL,
                      `delivery_uid` char(32) NOT NULL,
                      `status` varchar(16) NOT NULL DEFAULT 'pending',
                      `attempts` int(11) NOT NULL DEFAULT '0',
                      `next_attempt_at` int(11) NOT NULL DEFAULT '0',
                      `last_http_status` int(11) NOT NULL DEFAULT '0',
                      `last_error` varchar(255) NOT NULL DEFAULT '',
                      `created_at` int(11) NOT NULL,
                      `updated_at` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`delivery_id`),
                      UNIQUE KEY `idx_event_subscription` (`event_id`,`subscription_id`),
                      KEY `idx_due` (`status`,`next_attempt_at`)
                    )");

        $query_result = xtc_db_query("SHOW COLUMNS FROM `" . TABLE_ADMIN_ACCESS . "`");
        $db_table_rows = array();
        while ($row = xtc_db_fetch_array($query_result)) {
            $db_table_rows[] = $row['Field'];
        }

        if (!in_array('api_access', $db_table_rows)) {
            xtc_db_query("ALTER TABLE `" . TABLE_ADMIN_ACCESS . "` ADD `api_access` INT(1) NOT NULL DEFAULT 0");
            xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `api_access` = 1 WHERE `customers_id` = 1");
            xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `api_access` = 1 WHERE `customers_id` = " . (int)$_SESSION['customer_id']);
            xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `api_access` = 2 WHERE `customers_id` = 'groups'");
        }

        $this->update();
    }

    public function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_API_ACCESS_%'");
    }

    // keys
    public function keys()
    {
        return array(
            'MODULE_API_ACCESS_STATUS',
            'MODULE_API_ACCESS_WEBHOOKS_STATUS',
        );
    }
}
