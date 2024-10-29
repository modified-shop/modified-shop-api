<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // dhl
  $app->get('/dhl/{id}',                      \api\v1\Service\Dhl\GetDhl::class);

  // delete dhl
  $app->delete('/dhl/{id}',                   \api\v1\Service\Dhl\DeleteDhl::class);
