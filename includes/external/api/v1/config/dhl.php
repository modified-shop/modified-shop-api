<?php

/**
 * /includes/external/api/v1/config/dhl.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// dhl
$app->get('/dhl/{id}', \api\v1\Service\Dhl\GetDhl::class);

// delete dhl
$app->delete('/dhl/{id}', \api\v1\Service\Dhl\DeleteDhl::class);
