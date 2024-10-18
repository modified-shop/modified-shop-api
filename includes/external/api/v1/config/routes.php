<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  use Slim\App;
  use Slim\Routing\RouteCollectorProxy;
  use Tuupola\Middleware\HttpBasicAuthentication;
  use Tuupola\Middleware\JwtAuthentication;
  use api\v1\Auth\Authentication;

  return function (App $app) {
      $app->post('/v1/oauth', \api\v1\Auth\JwtAuth::class)->add(Authentication::class);
      
      // order
      $app->group(
          '/v1',
          function (RouteCollectorProxy $app) {
          
              // get categories
              $app->get('/categories',                    \api\v1\Service\Category\GetAllCategories::class);
              $app->get('/categories/{id}',               \api\v1\Service\Category\GetSingleCategory::class);
              $app->get('/categories/{id}/categories',    \api\v1\Service\Category\GetCategory::class);
              $app->get('/categories/{id}/description',   \api\v1\Service\Category\GetCategoryDescription::class);

              // insert categories
              $app->post('/categories',                   \api\v1\Service\Category\InsertCategory::class);
              $app->post('/categories/categories',        \api\v1\Service\Category\InsertUpdateCategory::class);
              $app->post('/categories/{id}/description',  \api\v1\Service\Category\InsertUpdateDescription::class);
              $app->post('/categories/{id}/images',       \api\v1\Service\Category\InsertUpdateImages::class);

              // update categories
              $app->put('/categories/{id}',               \api\v1\Service\Category\UpdateCategory::class);
              $app->put('/categories/{id}/categories',    \api\v1\Service\Category\InsertUpdateCategory::class);
              $app->put('/categories/{id}/description',   \api\v1\Service\Category\InsertUpdateDescription::class);

              // delete categories
              $app->delete('/categories/{id}',            \api\v1\Service\Category\DeleteCategory::class);
              $app->delete('/categories/{id}/images',     \api\v1\Service\Category\DeleteImages::class);



              // get orders
              $app->get('/orders',                        \api\v1\Service\Order\GetAllOrders::class);
              $app->get('/orders/{id}',                   \api\v1\Service\Order\GetSingleOrder::class);
              
              // update orders
              $app->post('/orders/status/{id}',           \api\v1\Service\Order\UpdateOrderStatus::class);
              $app->post('/orders/tracking/{id}',         \api\v1\Service\Order\InsertOrderTracking::class);

              // delete orders
              $app->delete('/orders/{id}',                \api\v1\Service\Order\DeleteOrder::class);
              
              
              
              // customers
              $app->get('/customers',                           \api\v1\Service\Customer\GetCustomers::class);
              $app->get('/customers/{id}',                      \api\v1\Service\Customer\GetSingleCustomer::class);
              $app->get('/customers/{id}/info',                 \api\v1\Service\Customer\getCustomerInfo::class);
              $app->get('/customers/{id}/ip',                   \api\v1\Service\Customer\getCustomerIp::class);
              $app->get('/customers/{id}/memo',                 \api\v1\Service\Customer\getCustomerMemos::class);
              $app->get('/customers/{id}/address_book',         \api\v1\Service\Customer\GetCustomerAddressBooks::class);
              $app->get('/customers/{id}/address_book/{aid}',   \api\v1\Service\Customer\GetCustomerAddressBook::class);
              
              // delete customers
              $app->delete('/customers/{id}',                   \api\v1\Service\Customer\DeleteCustomer::class);
                            
              
              
              // products
              $app->get('/products',                            \api\v1\Service\Product\GetProducts::class);
              $app->get('/products/{id}',                       \api\v1\Service\Product\GetSingleProduct::class);
              $app->get('/products/{id}/description',           \api\v1\Service\Product\GetProductDescription::class);
              $app->get('/products/{id}/categories',            \api\v1\Service\Product\GetProductCategories::class);
              $app->get('/products/{id}/images',                \api\v1\Service\Product\GetProductImages::class);
              $app->get('/products/{id}/xsell',                 \api\v1\Service\Product\GetProductXsell::class);
              $app->get('/products/{id}/attributes',            \api\v1\Service\Product\GetProductAttributes::class);
              $app->get('/products/{id}/tags',                  \api\v1\Service\Product\GetProductTags::class);
              $app->get('/products/{id}/content',               \api\v1\Service\Product\GetProductContent::class);
              $app->get('/products/{id}/specials',              \api\v1\Service\Product\GetProductSpecials::class);
              $app->get('/products/{id}/reviews',               \api\v1\Service\Product\GetProductReviews::class);
              $app->get('/products/{id}/offer',                 \api\v1\Service\Product\GetProductPersonalOffer::class);
              
              // insert products
              $app->post('/products',                           \api\v1\Service\Product\InsertProduct::class);
              $app->post('/products/products',                  \api\v1\Service\Product\InsertUpdateProduct::class);
              $app->post('/products/{id}/description',          \api\v1\Service\Product\InsertUpdateDescription::class);
              $app->post('/products/{id}/categories',           \api\v1\Service\Product\InsertUpdateCategories::class);
              $app->post('/products/{id}/image',                \api\v1\Service\Product\InsertUpdateImage::class);
              $app->post('/products/{id}/images',               \api\v1\Service\Product\InsertUpdateImages::class);

              // update products
              $app->put('/products/{id}',                       \api\v1\Service\Product\UpdateProduct::class);
              $app->put('/products/{id}/products',              \api\v1\Service\Product\InsertUpdateProduct::class);
              $app->put('/products/{id}/description',           \api\v1\Service\Product\InsertUpdateDescription::class);
              $app->put('/products/{id}/categories',            \api\v1\Service\Product\InsertUpdateCategories::class);

              // delete products
              $app->delete('/products/{id}',                                \api\v1\Service\Product\DeleteProduct::class);
              $app->delete('/products/{id}/categories/{cid}',               \api\v1\Service\Product\DeleteCategory::class);
              $app->delete('/products/{id}/image',                          \api\v1\Service\Product\DeleteImage::class);
              $app->delete('/products/{id}/images/{iid}',                   \api\v1\Service\Product\DeleteImages::class);
              $app->delete('/products/{id}/xsell/{xid}',                    \api\v1\Service\Product\DeleteXsell::class);
              $app->delete('/products/{id}/attributes/{aid}',               \api\v1\Service\Product\DeleteAttributes::class);
              $app->delete('/products/{id}/tags/{tid}',                     \api\v1\Service\Product\DeleteTags::class);
              $app->delete('/products/{id}/content/{cid}',                  \api\v1\Service\Product\DeleteContents::class);
              $app->delete('/products/{id}/specials/{sid}',                 \api\v1\Service\Product\DeleteSpecials::class);
              $app->delete('/products/{id}/reviews/{rid}',                  \api\v1\Service\Product\DeleteReviews::class);
              $app->delete('/products/{id}/offer/{cid}/{pid}',              \api\v1\Service\Product\DeletePersonalOffer::class);
              
              
              
              // dhl
              $app->get('/dhl/{id}',                      \api\v1\Service\Dhl\GetDhl::class);
              $app->delete('/dhl/{id}',                   \api\v1\Service\Dhl\DeleteDhl::class);
          }
      )->add(JwtAuthentication::class);
  };
