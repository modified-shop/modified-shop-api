<?php

/**
 * /includes/external/api/v1/config/campaigns.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// campaigns
$app->get('/campaigns', \api\v1\Service\Campaign\GetCampaigns::class);
$app->get('/campaigns/{id}/ip', \api\v1\Service\Campaign\GetCampaignsIp::class);

// insert campaigns
$app->post('/campaigns', \api\v1\Service\Campaign\InsertCampaign::class);
$app->post('/campaigns/{id}', \api\v1\Service\Campaign\InsertUpdateCampaign::class);

// update campaigns
$app->put('/campaigns/{id}', \api\v1\Service\Campaign\InsertUpdateCampaign::class);

// delete campaigns
$app->delete('/campaigns/{id}', \api\v1\Service\Campaign\DeleteCampaign::class);
