<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // newsletters
  $app->get('/newsletters/recipients',                       \api\v1\Service\Newsletter\GetNewsletterRecipients::class);
  $app->get('/newsletters/recipients/history',               \api\v1\Service\Newsletter\GetNewsletterRecipientsHistory::class);
  $app->get('/newsletters/recipients/{id}',                  \api\v1\Service\Newsletter\GetSingleNewsletterRecipients::class);
  $app->get('/newsletters/recipients/history/{id}',          \api\v1\Service\Newsletter\GetSingleNewsletterRecipientsHistory::class);

  // insert newsletters
  $app->post('/newsletters/recipients',                      \api\v1\Service\Newsletter\InsertNewsletterRecipients::class);
  $app->post('/newsletters/recipients/{id}',                 \api\v1\Service\Newsletter\InsertUpdateNewsletterRecipients::class);

  // update newsletters
  $app->put('/newsletters/recipients/{id}',                  \api\v1\Service\Newsletter\InsertUpdateNewsletterRecipients::class);

  // delete newsletters
  $app->delete('/newsletters/recipients/{id}',               \api\v1\Service\Newsletter\DeleteNewsletterRecipients::class);
