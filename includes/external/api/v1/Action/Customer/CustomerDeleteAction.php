<?php

/**
 * /includes/external/api/v1/Action/Customer/CustomerDeleteAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Customer;

use api\v1\Action\BaseAction;
use api\v1\Utility\LoggerHandler;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Service.
 */
trait CustomerDeleteAction
{
    /**
     * Delete a customer by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteCustomer(int $customerId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS . "
                                           WHERE customers_id = '" . (int)$customerId . "'");
        if (xtc_db_num_rows($customer_query) < 1) {
            return $this->errormessage(sprintf('Customer not found: %s', $customerId));
        } else {
            // disable Exception
            $this->throw_exception = false;

            $this->DeleteInfo($customerId);
            $this->DeleteAllIp($customerId);
            $this->DeleteAllMemo($customerId);
            $this->DeleteAllStatusHistory($customerId);
            $this->DeleteAllAddressBook($customerId);
            $this->DeleteAllBasket($customerId);
            $this->DeleteAllWishlist($customerId);

            xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '" . (int)$customerId . "'");
        }

        $this->logger->info(sprintf('Customer deleted successfully: %s', $customerId));
    }

    /**
     * Delete a customer info by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteInfo(int $customerId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_INFO . "
                                           WHERE customers_info_id = '" . (int)$customerId . "'");
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer info not found: %s', $customerId));
        } else {
            xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_INFO . " 
                                  WHERE customers_info_id = '" . (int)$customerId . "'");
        }
    }

    /**
     * Delete a customer address book by the given customer id and customer ip id.
     *
     * @param int $customerId The customer id
     * @param int $customerIpId The customer ip id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteIp(int $customerId, int $customerIpId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $where = '';
        if ($customerIpId > 0) {
            $where = "AND customers_ip_id = '" . (int)$customerIpId . "'";
        }

        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_IP . "
                                           WHERE customers_id = '" . (int)$customerId . "'
                                                 " . $where);
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer ip not found: %s', $customerId));
        } else {
            while ($customer = xtc_db_fetch_array($customer_query)) {
                xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_IP . " 
                                      WHERE customers_id = '" . (int)$customerId . "'
                                        AND customers_ip_id = '" . (int)$customer['customers_ip_id'] . "'");
            }
        }
    }

    /**
     * Delete all customer address book by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteAllIp(int $customerId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $this->DeleteIp($customerId, 0);
    }

    /**
     * Delete a customer memo by the given customer id and memo id.
     *
     * @param int $customerId The customer id
     * @param int $memoId The memo id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteMemo(int $customerId, int $memoId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $where = '';
        if ($memoId > 0) {
            $where = "AND memo_id = '" . (int)$memoId . "'";
        }

        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_MEMO . "
                                           WHERE customers_id = '" . (int)$customerId . "'
                                                 " . $where);
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer memo not found: %s', $customerId));
        } else {
            while ($customer = xtc_db_fetch_array($customer_query)) {
                xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_MEMO . " 
                                      WHERE customers_id = '" . (int)$customerId . "'
                                        AND memo_id = '" . (int)$customer['memo_id'] . "'");
            }
        }
    }

    /**
     * Delete all customer memo by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteAllMemo(int $customerId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $this->DeleteMemo($customerId, 0);
    }

    /**
     * Delete a customer history by the given customer id and status history id.
     *
     * @param int $customerId The customer id
     * @param int $customerStatusHistoryId The status history id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteStatusHistory(int $customerId, int $customerStatusHistoryId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $where = '';
        if ($customerStatusHistoryId > 0) {
            $where = "AND customers_status_history_id = '" . (int)$customerStatusHistoryId . "'";
        }

        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_STATUS_HISTORY . "
                                           WHERE customers_id = '" . (int)$customerId . "'
                                                 " . $where);
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer status history not found: %s', $customerId));
        } else {
            while ($customer = xtc_db_fetch_array($customer_query)) {
                xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_STATUS_HISTORY . " 
                                      WHERE customers_id = '" . (int)$customerId . "'
                                        AND customers_status_history_id = '" . (int)$customer['customers_status_history_id'] . "'");
            }
        }
    }

    /**
     * Delete all customer history by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteAllStatusHistory(int $customerId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $this->DeleteStatusHistory($customerId, 0);
    }

    /**
     * Delete a customer address book by the given customer id and address book id.
     *
     * @param int $customerId The customer id
     * @param int $addressBookId The address book id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteAddressBook(int $customerId, int $addressBookId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $where = '';
        if ($addressBookId > 0) {
            $where = "AND address_book_id = '" . (int)$addressBookId . "'";
        }

        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_ADDRESS_BOOK . "
                                           WHERE customers_id = '" . (int)$customerId . "'
                                                 " . $where);
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer address book not found: %s', $customerId));
        } else {
            while ($customer = xtc_db_fetch_array($customer_query)) {
                xtc_db_query("DELETE FROM " . TABLE_ADDRESS_BOOK . " 
                                      WHERE customers_id = '" . (int)$customerId . "'
                                        AND address_book_id = '" . (int)$customer['address_book_id'] . "'");
            }
        }
    }

    /**
     * Delete all customer address book by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteAllAddressBook(int $customerId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $this->DeleteAddressBook($customerId, 0);
    }

    /**
     * Delete a customer basket by the given customer id and customer basket id.
     *
     * @param int $customerId The customer id
     * @param int $customersBasketId The customer basket id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteBasket(int $customerId, int $customersBasketId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $where = '';
        if ($customersBasketId > 0) {
            $where = "AND customers_basket_id = '" . (int)$customersBasketId . "'";
        }

        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_BASKET . "
                                           WHERE customers_id = '" . (int)$customerId . "'
                                                 " . $where);
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer basket not found: %s', $customerId));
        } else {
            while ($customer = xtc_db_fetch_array($customer_query)) {
                xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " 
                                      WHERE customers_id = '" . (int)$customerId . "'
                                        AND customers_basket_id = '" . (int)$customer['customers_basket_id'] . "'");

                xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " 
                                      WHERE customers_id = '" . (int)$customerId . "'
                                        AND products_id = '" . xtc_db_input($customer['products_id']) . "'");
            }
        }
    }

    /**
     * Delete all customer basket by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteAllBasket(int $customerId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $this->DeleteBasket($customerId, 0);
    }

    /**
     * Delete a customer wishlist by the given customer id and customer basket id.
     *
     * @param int $customerId The customer id
     * @param int $customersBasketId The customer basket id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteWishlist(int $customerId, int $customersBasketId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $where = '';
        if ($customersBasketId > 0) {
            $where = "AND customers_basket_id = '" . (int)$customersBasketId . "'";
        }

        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_WISHLIST . "
                                           WHERE customers_id = '" . (int)$customerId . "'
                                                 " . $where);
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer wishlist not found: %s', $customerId));
        } else {
            while ($customer = xtc_db_fetch_array($customer_query)) {
                xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_WISHLIST . " 
                                      WHERE customers_id = '" . (int)$customerId . "'
                                        AND customers_basket_id = '" . (int)$customer['customers_basket_id'] . "'");

                xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_WISHLIST_ATTRIBUTES . " 
                                      WHERE customers_id = '" . (int)$customerId . "'
                                        AND products_id = '" . xtc_db_input($customer['products_id']) . "'");
            }
        }
    }

    /**
     * Delete all customer wishlist by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return void
     */
    public function DeleteAllWishlist(int $customerId): void
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $this->DeleteWishlist($customerId, 0);
    }
}
