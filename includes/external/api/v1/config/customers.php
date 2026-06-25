<?php

/**
 * /includes/external/api/v1/config/customers.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// customers
$app->get('/customers', \api\v1\Service\Customer\GetCustomers::class);
$app->get('/customers/whos_online', \api\v1\Service\Customer\GetWhosOnline::class);
$app->get('/customers/{id}', \api\v1\Service\Customer\GetSingleCustomer::class);
$app->get('/customers/{id}/info', \api\v1\Service\Customer\GetCustomerInfo::class);
$app->get('/customers/{id}/ip', \api\v1\Service\Customer\GetCustomerIp::class);
$app->get('/customers/{id}/memo', \api\v1\Service\Customer\GetCustomerMemos::class);
$app->get('/customers/{id}/status_history', \api\v1\Service\Customer\GetCustomerStatusHistory::class);
$app->get('/customers/{id}/address_book', \api\v1\Service\Customer\GetCustomerAddressBooks::class);
$app->get('/customers/{id}/address_book/{aid}', \api\v1\Service\Customer\GetSingleCustomerAddressBook::class);
$app->get('/customers/{id}/basket', \api\v1\Service\Customer\GetCustomerBasket::class);
$app->get('/customers/{id}/wishlist', \api\v1\Service\Customer\GetCustomerWishlist::class);

// insert customers
$app->post('/customers', \api\v1\Service\Customer\InsertCustomer::class);
$app->post('/customers/customers', \api\v1\Service\Customer\InsertUpdateCustomer::class);
$app->post('/customers/{id}/info', \api\v1\Service\Customer\InsertUpdateInfo::class);
$app->post('/customers/{id}/memo', \api\v1\Service\Customer\InsertMemo::class);
$app->post('/customers/{id}/memo/{mid}', \api\v1\Service\Customer\InsertUpdateMemo::class);
$app->post('/customers/{id}/address_book', \api\v1\Service\Customer\InsertAddressBook::class);
$app->post('/customers/{id}/address_book/{aid}', \api\v1\Service\Customer\InsertUpdateAddressBook::class);

// update customers
$app->put('/customers', \api\v1\Service\Customer\InsertCustomer::class);
$app->put('/customers/{id}', \api\v1\Service\Customer\InsertUpdateCustomer::class);
$app->put('/customers/{id}/info', \api\v1\Service\Customer\InsertUpdateInfo::class);
$app->put('/customers/{id}/memo/{mid}', \api\v1\Service\Customer\InsertUpdateMemo::class);
$app->put('/customers/{id}/address_book/{aid}', \api\v1\Service\Customer\InsertUpdateAddressBook::class);

// delete customers
$app->delete('/customers/{id}', \api\v1\Service\Customer\DeleteCustomer::class);
$app->delete('/customers/{id}/info', \api\v1\Service\Customer\DeleteInfo::class);
$app->delete('/customers/{id}/ip', \api\v1\Service\Customer\DeleteAllIp::class);
$app->delete('/customers/{id}/ip/{iid}', \api\v1\Service\Customer\DeleteIp::class);
$app->delete('/customers/{id}/memo', \api\v1\Service\Customer\DeleteAllMemo::class);
$app->delete('/customers/{id}/memo/{mid}', \api\v1\Service\Customer\DeleteMemo::class);
$app->delete('/customers/{id}/status_history', \api\v1\Service\Customer\DeleteAllStatusHistory::class);
$app->delete('/customers/{id}/status_history/{sid}', \api\v1\Service\Customer\DeleteStatusHistory::class);
$app->delete('/customers/{id}/address_book', \api\v1\Service\Customer\DeleteAllAddressBook::class);
$app->delete('/customers/{id}/address_book/{aid}', \api\v1\Service\Customer\DeleteAddressBook::class);
$app->delete('/customers/{id}/basket', \api\v1\Service\Customer\DeleteAllBasket::class);
$app->delete('/customers/{id}/basket/{bid}', \api\v1\Service\Customer\DeleteBasket::class);
$app->delete('/customers/{id}/wishlist', \api\v1\Service\Customer\DeleteAllWishlist::class);
$app->delete('/customers/{id}/wishlist/{bid}', \api\v1\Service\Customer\DeleteWishlist::class);
