<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // customers
  $app->get('/customers',                           \api\v1\Service\Customer\GetCustomers::class);
  $app->get('/customers/{id}',                      \api\v1\Service\Customer\GetSingleCustomer::class);
  $app->get('/customers/{id}/info',                 \api\v1\Service\Customer\GetCustomerInfo::class);
  $app->get('/customers/{id}/ip',                   \api\v1\Service\Customer\GetCustomerIp::class);
  $app->get('/customers/{id}/memo',                 \api\v1\Service\Customer\GetCustomerMemos::class);
  $app->get('/customers/{id}/address_book',         \api\v1\Service\Customer\GetCustomerAddressBooks::class);
  $app->get('/customers/{id}/address_book/{aid}',   \api\v1\Service\Customer\GetCustomerAddressBook::class);
  
  // delete customers
  $app->delete('/customers/{id}',                   \api\v1\Service\Customer\DeleteCustomer::class);
