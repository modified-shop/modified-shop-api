<?php

/**
 * /includes/external/api/v1/bin/dispatch.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// CLI alternative to GET /api/v1/events/dispatch for hosts whose cron can run
// php directly:  */5 * * * * php /path/to/shop/includes/external/api/v1/bin/dispatch.php
// No secret is needed here: whoever can execute shell commands on the host is
// already inside the trust boundary the secret protects over HTTP.

if (PHP_SAPI !== 'cli') {
    exit(1);
}

// shop bootstrap (defines DIR_FS_EXTERNAL, xtc_db_* and the configuration constants)
require_once(__DIR__ . '/../../../../../includes/application_top_callback.php');

if (!defined('MODULE_API_ACCESS_WEBHOOKS_STATUS') || MODULE_API_ACCESS_WEBHOOKS_STATUS != 'true') {
    fwrite(STDERR, "webhooks are disabled (MODULE_API_ACCESS_WEBHOOKS_STATUS)\n");
    exit(1);
}

// class autoloader (same one the Slim app uses)
require_once(DIR_FS_EXTERNAL . 'Slim/autoload.php');

$settings = require(DIR_FS_EXTERNAL . 'api/v1/config/settings.php');

$dispatcher = new \api\v1\Webhook\Dispatcher(
    new \api\v1\Utility\LoggerHandler($settings['logger'])
);

echo json_encode($dispatcher->run()), "\n";
