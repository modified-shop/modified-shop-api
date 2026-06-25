<?php

/**
 * /includes/external/api/v1/config/configurations.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// configurations
  $app->get('/configurations/groups',                 \api\v1\Service\Configuration\GetConfigurationGroups::class);
  $app->get('/configurations/{id}',                   \api\v1\Service\Configuration\GetConfiguration::class);
  $app->get('/configurations/groups/{id}',            \api\v1\Service\Configuration\GetSingleConfigurationGroup::class);
 