<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // configurations
  $app->get('/configurations/groups',                 \api\v1\Service\Configuration\GetConfigurationGroups::class);
  $app->get('/configurations/{id}',                   \api\v1\Service\Configuration\GetConfiguration::class);
  $app->get('/configurations/groups/{id}',            \api\v1\Service\Configuration\GetSingleConfigurationGroup::class);
 