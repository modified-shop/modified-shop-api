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
              $app->get('/order/{id}',                    \api\v1\Service\Order\GetSingleOrder::class);
              
              // update orders
              $app->post('/order/status/{id}',            \api\v1\Service\Order\UpdateOrderStatus::class);
              $app->post('/order/tracking/{id}',          \api\v1\Service\Order\InsertOrderTracking::class);

              // delete orders
              $app->delete('/order/{id}',                 \api\v1\Service\Order\DeleteOrder::class);
              
              
              
              // customers
              $app->get('/customers',                         \api\v1\Service\Customer\GetCustomers::class);
              $app->get('/customer/{id}',                     \api\v1\Service\Customer\GetCustomer::class);
              $app->get('/customer/{id}/address_book',        \api\v1\Service\Customer\GetCustomerAddressBooks::class);
              $app->get('/customer/{id}/address_book/{aid}',  \api\v1\Service\Customer\GetCustomerAddressBook::class);
              
              // delete customers
              $app->delete('/customers/{id}',                 \api\v1\Service\Customer\DeleteCustomer::class);
                            
              
              
              // products
              $app->get('/products',                      \api\v1\Service\Product\GetProducts::class);
              $app->get('/products/{id}',                 \api\v1\Service\Product\GetProduct::class);
              $app->get('/products/{id}/description',     \api\v1\Service\Product\GetProductDescription::class);
              $app->get('/products/{id}/categories',      \api\v1\Service\Product\GetProductCategories::class);
              $app->get('/products/{id}/images',          \api\v1\Service\Product\GetProductImages::class);
              $app->get('/products/{id}/xsell',           \api\v1\Service\Product\GetProductXsell::class);
              $app->get('/products/{id}/attributes',      \api\v1\Service\Product\GetProductAttributes::class);
              
              // insert products
              $app->post('/products',                     \api\v1\Service\Product\InsertProduct::class);
              $app->post('/products/products',            \api\v1\Service\Product\InsertUpdateProduct::class);
              $app->post('/products/{id}/description',    \api\v1\Service\Product\InsertUpdateDescription::class);

              // update products
              $app->put('/products/{id}',                \api\v1\Service\Product\UpdateProduct::class);
              $app->put('/products/{id}/products',       \api\v1\Service\Product\InsertUpdateProduct::class);
              $app->put('/products/{id}/description',    \api\v1\Service\Product\InsertUpdateDescription::class);

              // delete products
              $app->delete('/products/{id}',               \api\v1\Service\Product\DeleteProduct::class);
              
              
              
              // dhl
              $app->get('/dhl/{id}',                      \api\v1\Service\Dhl\GetDhl::class);
              $app->delete('/dhl/{id}',                   \api\v1\Service\Dhl\DeleteDhl::class);
          }
      )->add(JwtAuthentication::class);
  };
