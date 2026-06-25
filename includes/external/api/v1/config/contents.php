<?php

/**
 * /includes/external/api/v1/config/contents.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// contents
  $app->get('/contents',                          \api\v1\Service\Content\GetContents::class);
  $app->get('/contents/fileflag',                 \api\v1\Service\Content\GetContentFileFlag::class);
  $app->get('/contents/{id}',                     \api\v1\Service\Content\GetSingleContent::class);
  $app->get('/contents/{id}/content',             \api\v1\Service\Content\GetContentContent::class);

  // insert contents
  $app->post('/contents',                         \api\v1\Service\Content\InsertContent::class);
  $app->post('/contents/{id}',                    \api\v1\Service\Content\InsertUpdateContent::class);
  $app->post('/contents/{id}/content',            \api\v1\Service\Content\InsertUpdateContentContent::class);

  // update contents
  $app->put('/contents/{id}',                     \api\v1\Service\Content\InsertUpdateContent::class);
  $app->put('/contents/{id}/content',             \api\v1\Service\Content\InsertUpdateContentContent::class);

  // delete contents
  $app->delete('/contents/{id}',                  \api\v1\Service\Content\DeleteContent::class);
  $app->delete('/contents/{id}/content',          \api\v1\Service\Content\DeleteAllContentContent::class);
  $app->delete('/contents/{id}/content/{cid}',    \api\v1\Service\Content\DeleteContentContent::class);
