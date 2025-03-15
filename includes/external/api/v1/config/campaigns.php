<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // campaigns
  $app->get('/campaigns',                       \api\v1\Service\Campaign\GetCampaigns::class);
  $app->get('/campaigns/{id}/ip',               \api\v1\Service\Campaign\GetCampaignsIp::class);

  // insert campaigns
  $app->post('/campaigns',                      \api\v1\Service\Campaign\InsertCampaign::class);
  $app->post('/campaigns/{id}',                 \api\v1\Service\Campaign\InsertUpdateCampaign::class);

  // update campaigns
  $app->put('/campaigns/{id}',                  \api\v1\Service\Campaign\InsertUpdateCampaign::class);

  // delete campaigns
  $app->delete('/campaigns/{id}',               \api\v1\Service\Campaign\DeleteCampaign::class);
