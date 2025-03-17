<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // contents
  $app->get('/contents',                       \api\v1\Service\Content\GetContents::class);
  $app->get('/contents/{id}',                  \api\v1\Service\Content\GetSingleContent::class);
  $app->get('/contents/{id}/content',          \api\v1\Service\Content\GetContentContent::class);

  // insert contents
  $app->post('/contents',                      \api\v1\Service\Content\InsertContent::class);
  $app->post('/contents/{id}',                 \api\v1\Service\Content\InsertUpdateContent::class);
  $app->post('/contents/{id}/content',         \api\v1\Service\Content\InsertUpdateContentContent::class);

  // update contents
  $app->put('/contents/{id}',                  \api\v1\Service\Content\InsertUpdateContent::class);

  // delete contents
  $app->delete('/contents/{id}',               \api\v1\Service\Content\DeleteContent::class);
  $app->delete('/contents/{id}/content',       \api\v1\Service\Content\DeleteAllContentContent::class);
