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
  $app->get('/campaign',                           \api\v1\Service\Campaign\GetCampaigns::class);
  $app->get('/campaign/{id}/ip',                   \api\v1\Service\Campaign\GetCampaignsIp::class);
