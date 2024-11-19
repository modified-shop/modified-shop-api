<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // products
  $app->get('/products',                            \api\v1\Service\Product\GetProducts::class);
  $app->get('/products/{id}',                       \api\v1\Service\Product\GetSingleProduct::class);
  $app->get('/products/{id}/description',           \api\v1\Service\Product\GetProductDescription::class);
  $app->get('/products/{id}/categories',            \api\v1\Service\Product\GetProductCategories::class);
  $app->get('/products/{id}/images',                \api\v1\Service\Product\GetProductImages::class);
  $app->get('/products/{id}/images/description',    \api\v1\Service\Product\GetProductImagesDescription::class);
  $app->get('/products/{id}/xsell',                 \api\v1\Service\Product\GetProductXsell::class);
  $app->get('/products/{id}/attributes',            \api\v1\Service\Product\GetProductAttributes::class);
  $app->get('/products/{id}/tags',                  \api\v1\Service\Product\GetProductTags::class);
  $app->get('/products/{id}/content',               \api\v1\Service\Product\GetProductContent::class);
  $app->get('/products/{id}/specials',              \api\v1\Service\Product\GetProductSpecials::class);
  $app->get('/products/{id}/reviews',               \api\v1\Service\Product\GetProductReviews::class);
  $app->get('/products/{id}/offer',                 \api\v1\Service\Product\GetProductPersonalOffer::class);
  
  // insert products
  $app->post('/products',                                 \api\v1\Service\Product\InsertProduct::class);
  $app->post('/products/products',                        \api\v1\Service\Product\InsertUpdateProduct::class);
  $app->post('/products/{id}/description',                \api\v1\Service\Product\InsertUpdateDescription::class);
  $app->post('/products/{id}/categories',                 \api\v1\Service\Product\InsertUpdateCategories::class);
  $app->post('/products/{id}/image',                      \api\v1\Service\Product\InsertUpdateImage::class);
  $app->post('/products/{id}/images',                     \api\v1\Service\Product\InsertUpdateImages::class);
  $app->post('/products/{id}/images/{iid}/description',   \api\v1\Service\Product\InsertUpdateImagesDescription::class);
  $app->post('/products/{id}/xsell',                      \api\v1\Service\Product\InsertUpdateXsell::class);
  $app->post('/products/{id}/attributes',                 \api\v1\Service\Product\InsertUpdateAttributes::class);
  $app->post('/products/{id}/tags',                       \api\v1\Service\Product\InsertUpdateTags::class);
  $app->post('/products/{id}/content',                    \api\v1\Service\Product\InsertUpdateContent::class);
  $app->post('/products/{id}/specials',                   \api\v1\Service\Product\InsertUpdateSpecials::class);
  $app->post('/products/{id}/offer',                      \api\v1\Service\Product\InsertUpdatePersonalOffer::class);

  // update products
  $app->put('/products/{id}',                             \api\v1\Service\Product\UpdateProduct::class);
  $app->put('/products/{id}/products',                    \api\v1\Service\Product\InsertUpdateProduct::class);
  $app->put('/products/{id}/description',                 \api\v1\Service\Product\InsertUpdateDescription::class);
  $app->put('/products/{id}/categories',                  \api\v1\Service\Product\InsertUpdateCategories::class);
  $app->put('/products/{id}/images/{iid}/description',    \api\v1\Service\Product\InsertUpdateImagesDescription::class);
  $app->put('/products/{id}/xsell',                       \api\v1\Service\Product\InsertUpdateXsell::class);
  $app->put('/products/{id}/attributes',                  \api\v1\Service\Product\InsertUpdateAttributes::class);
  $app->put('/products/{id}/tags',                        \api\v1\Service\Product\InsertUpdateTags::class);
  $app->put('/products/{id}/specials',                    \api\v1\Service\Product\InsertUpdateSpecials::class);
  $app->put('/products/{id}/offer',                       \api\v1\Service\Product\InsertUpdatePersonalOffer::class);

  // delete products
  $app->delete('/products/{id}',                                \api\v1\Service\Product\DeleteProduct::class);
  $app->delete('/products/{id}/categories',                     \api\v1\Service\Product\DeleteAllCategory::class);
  $app->delete('/products/{id}/categories/{cid}',               \api\v1\Service\Product\DeleteCategory::class);
  $app->delete('/products/{id}/image',                          \api\v1\Service\Product\DeleteImage::class);
  $app->delete('/products/{id}/images',                         \api\v1\Service\Product\DeleteAllImages::class);
  $app->delete('/products/{id}/images/{iid}',                   \api\v1\Service\Product\DeleteImages::class);
  $app->delete('/products/{id}/images/{iid}/description',       \api\v1\Service\Product\DeleteImagesDescription::class);
  $app->delete('/products/{id}/xsell',                          \api\v1\Service\Product\DeleteAllXsell::class);
  $app->delete('/products/{id}/xsell/{xid}',                    \api\v1\Service\Product\DeleteXsell::class);
  $app->delete('/products/{id}/attributes',                     \api\v1\Service\Product\DeleteAllAttributes::class);
  $app->delete('/products/{id}/attributes/{aid}',               \api\v1\Service\Product\DeleteAttributes::class);
  $app->delete('/products/{id}/tags',                           \api\v1\Service\Product\DeleteAllTags::class);
  $app->delete('/products/{id}/tags/{tid}',                     \api\v1\Service\Product\DeleteTags::class);
  $app->delete('/products/{id}/content',                        \api\v1\Service\Product\DeleteAllContents::class);
  $app->delete('/products/{id}/content/{cid}',                  \api\v1\Service\Product\DeleteContents::class);
  $app->delete('/products/{id}/specials',                       \api\v1\Service\Product\DeleteAllSpecials::class);
  $app->delete('/products/{id}/specials/{sid}',                 \api\v1\Service\Product\DeleteSpecials::class);
  $app->delete('/products/{id}/reviews',                        \api\v1\Service\Product\DeleteAllReviews::class);
  $app->delete('/products/{id}/reviews/{rid}',                  \api\v1\Service\Product\DeleteReviews::class);
  $app->delete('/products/{id}/offer',                          \api\v1\Service\Product\DeleteAllPersonalOffers::class);
  $app->delete('/products/{id}/offer/{cid}',                    \api\v1\Service\Product\DeleteAllPersonalOffer::class);
  $app->delete('/products/{id}/offer/{cid}/{pid}',              \api\v1\Service\Product\DeletePersonalOffer::class);
