<?php

/**
 * /includes/external/api/v1/Action/Customer/CustomerGetAction.php
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
trait CustomerGetAction
{
    /**
     * Read a customer by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return array The customer data
     */
    public function GetCustomerDetails(int $customerId): array
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

            $result = [
                'customers' => $this->GetCustomer($customerId),
            ];

            if (isset($this->options['with'])) {
                $with = explode(',', $this->options['with']);
                if (in_array('info', $with) !== false) {
                    $result['customers_info'] = $this->GetCustomerInfo($customerId);
                }
                if (in_array('ip', $with) !== false) {
                    $result['customers_ip'] = $this->GetCustomerIp($customerId);
                }
                if (in_array('memo', $with) !== false) {
                    $result['customers_memo'] = $this->GetCustomerMemos($customerId);
                }
                if (in_array('history', $with) !== false) {
                    $result['customers_status_history'] = $this->GetCustomerStatusHistory($customerId);
                }
                if (in_array('address', $with) !== false) {
                    $result['address_book'] = $this->GetCustomerAddressBooks($customerId);
                }
                if (in_array('basket', $with) !== false) {
                    $result['customers_basket'] = $this->GetCustomerBasket($customerId);
                }
                if (in_array('wishlist', $with) !== false) {
                    $result['customers_wishlist'] = $this->GetCustomerWishlist($customerId);
                }
            }

            return $result;
        }
    }

    /**
     * Read a customer by the given customer id.
     *
     * @param int $customerId The customer id
     * @param mixed[] $options
     *
     * @throws Exception
     *
     * @return array The customer data
     */
    public function GetSingleCustomer(int $customerId, array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $result = $this->GetCustomerDetails($customerId);
        return $result;
    }

    /**
     * Read customers by given conditions
     *
     * @param mixed[] $options
     *
     * @return array The customer data
     */
    public function GetCustomers(array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        $conditions = [];
        if (isset($this->options['status']) && !empty(preg_replace('/[^\d\,]/', '', $this->options['status']))) {
            $conditions[] = " customers_status IN (" . preg_replace('/[^\d\,]/', '', $this->options['status']) . ") ";
        }
        if ((int)$this->options['from'] > 0) {
            $conditions[] = " customers_date_added >= '" . date('Y-m-d H:i:s', (int)$this->options['from']) . "' ";
        }
        if ((int)$this->options['to'] > 0) {
            $conditions[] = " customers_date_added <= '" . date('Y-m-d H:i:s', (int)$this->options['to']) . "' ";
        }

        $where = '';
        if (count($conditions) > 0) {
            $where = " WHERE " . implode(' AND ', $conditions);
        }

        $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM " . TABLE_CUSTOMERS . "
                                              " . $where);
        $count = xtc_db_fetch_array($count_query);

        if ($count['total'] < 1) {
            return $this->errormessage('no Customer found');
        }

        $data = [];
        $customers_query = xtc_db_query("SELECT customers_id
                                             FROM " . TABLE_CUSTOMERS . "
                                                  " . $where . "
                                         ORDER BY customers_date_added DESC
                                            LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($customers = xtc_db_fetch_array($customers_query)) {
            $data[] = $this->GetCustomerDetails($customers['customers_id']);
        }

        $result = [
            'paging' => [
                'total' => $count['total']
            ],
            'data' => $data
        ];

        if ($count['total'] > count($data)) {
            if ($this->options['page'] > 1) {
                $result['paging']['prev'] = HTTPS_SERVER . DIR_WS_CATALOG . ltrim($this->options['path'], '/') . '?' . xtc_get_all_get_params(array('page')) . 'page=' . ($this->options['page'] - 1);
            }
            if (((($this->options['page'] - 1) * $this->options['limit']) + $this->options['limit']) < $count['total']) {
                $result['paging']['next'] = HTTPS_SERVER . DIR_WS_CATALOG . ltrim($this->options['path'], '/') . '?' . xtc_get_all_get_params(array('page')) . 'page=' . ($this->options['page'] + 1);
            }
        }

        return $result;
    }

    /**
     * Read a customer by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return array The customer data
     */
    public function GetCustomer(int $customerId): array
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $customer = [];
        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS . "
                                           WHERE customers_id = '" . (int)$customerId . "'");
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer not found: %s', $customerId));
        } else {
            $customer = xtc_db_fetch_array($customer_query);

            // remove password
            unset($customer['customers_password']);
            unset($customer['customers_password_time']);
        }

        $result = $this->encode_request($customer);
        return $result;
    }

    /**
     * Read a customer info by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return array The customer data
     */
    public function GetCustomerInfo(int $customerId): array
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $info = [];
        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_INFO . "
                                           WHERE customers_info_id = '" . (int)$customerId . "'");
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer info not found: %s', $customerId));
        } else {
            $info = xtc_db_fetch_array($customer_query);
        }

        $result = $this->encode_request($info);
        return $result;
    }

    /**
     * Read a customer ip by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return array The customer data
     */
    public function GetCustomerIp(int $customerId): array
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $ip = [];
        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_IP . "
                                           WHERE customers_id = '" . (int)$customerId . "'");
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer ip not found: %s', $customerId));
        } else {
            $customers_ip_query = xtc_db_query("SELECT *
                                                    FROM " . TABLE_CUSTOMERS_IP . "
                                                   WHERE customers_id = '" . (int)$customerId . "'
                                                ORDER BY customers_ip_id");
            while ($customers_ip = xtc_db_fetch_array($customers_ip_query)) {
                $ip[] = $customers_ip;
            }
        }

        $result = $this->encode_request($ip);
        return $result;
    }

    /**
     * Read an customer memo by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return array The memo data
     */
    public function GetCustomerMemos(int $customerId): array
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $memo = [];
        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_MEMO . "
                                           WHERE customers_id = '" . (int)$customerId . "'");
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer memo not found: %s', $customerId));
        } else {
            $customers_memo_query = xtc_db_query("SELECT *
                                                    FROM " . TABLE_CUSTOMERS_MEMO . "
                                                   WHERE customers_id = '" . (int)$customerId . "'
                                                ORDER BY memo_id ASC");
            while ($customers_memo = xtc_db_fetch_array($customers_memo_query)) {
                $memo[] = $customers_memo;
            }
        }

        $result = $this->encode_request($memo);
        return $result;
    }

    /**
     * Read a customer status history by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return array The customer data
     */
    public function GetCustomerStatusHistory(int $customerId): array
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $status_history = [];
        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_STATUS_HISTORY . "
                                           WHERE customers_id = '" . (int)$customerId . "'");
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer status history not found: %s', $customerId));
        } else {
            $customers_status_history_query = xtc_db_query("SELECT *
                                                                FROM " . TABLE_CUSTOMERS_STATUS_HISTORY . "
                                                               WHERE customers_id = '" . (int)$customerId . "'
                                                            ORDER BY customers_status_history_id");
            while ($customers_status_history = xtc_db_fetch_array($customers_status_history_query)) {
                $status_history[] = $customers_status_history;
            }
        }

        $result = $this->encode_request($status_history);
        return $result;
    }

    /**
     * Read a customer address book by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return array The address book data
     */
    public function GetCustomerAddressBooks(int $customerId): array
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $address_books = [];
        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_ADDRESS_BOOK . "
                                           WHERE customers_id = '" . (int)$customerId . "'");
        if (xtc_db_num_rows($customer_query) < 1) {
            return $this->errormessage(sprintf('Customer addresses not found: %s', $customerId));
        } else {
            $address_book_query = xtc_db_query("SELECT address_book_id
                                                    FROM " . TABLE_ADDRESS_BOOK . " ab
                                                   WHERE customers_id = '" . (int)$customerId . "'
                                                ORDER BY address_book_id ASC");
            while ($address_book = xtc_db_fetch_array($address_book_query)) {
                $address_books[] = $this->GetSingleCustomerAddressBook($customerId, $address_book['address_book_id']);
            }
        }

        $result = $this->encode_request($address_books);
        return $result;
    }

    /**
     * Read a customer address book by the given customer id and address book id.
     *
     * @param int $customerId The customer id
     * @param int $addressBookId The address book id
     *
     * @throws Exception
     *
     * @return array The address book data
     */
    public function GetSingleCustomerAddressBook(int $customerId, int $addressBookId): array
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        if (empty($addressBookId)) {
            throw new Exception('Addressbook ID required');
        }

        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_ADDRESS_BOOK . "
                                           WHERE customers_id = '" . (int)$customerId . "'");
        if (xtc_db_num_rows($customer_query) < 1) {
            return $this->errormessage(sprintf('Customer address not found: %s', $customerId));
        } else {
            $address_book_query = xtc_db_query("SELECT ab.*,
                                                         c.countries_name,
                                                         c.countries_iso_code_2,
                                                         c.countries_iso_code_3,
                                                         z.zone_name,
                                                         z.zone_code
                                                    FROM " . TABLE_ADDRESS_BOOK . " ab
                                                    JOIN " . TABLE_COUNTRIES . " c
                                                         ON ab.entry_country_id = c.countries_id
                                               LEFT JOIN " . TABLE_ZONES . " z
                                                         ON ab.entry_country_id = z.zone_country_id
                                                            AND ab.entry_zone_id = z.zone_id
                                                   WHERE ab.customers_id = '" . (int)$customerId . "'
                                                     AND ab.address_book_id = '" . (int)$addressBookId . "'");
            $address_book = xtc_db_fetch_array($address_book_query);
        }

        $result = $this->encode_request($address_book);
        return $result;
    }

    /**
     * Read a customer basket by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return array The customer data
     */
    public function GetCustomerBasket(int $customerId): array
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $basket = [];
        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_BASKET . "
                                           WHERE customers_id = '" . (int)$customerId . "'");
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer basket not found: %s', $customerId));
        } else {
            $customers_basket_query = xtc_db_query("SELECT *
                                                        FROM " . TABLE_CUSTOMERS_BASKET . "
                                                       WHERE customers_id = '" . (int)$customerId . "'
                                                    ORDER BY customers_basket_id");
            while ($customers_basket = xtc_db_fetch_array($customers_basket_query)) {
                $customers_basket['attributes'] = [];
                $customers_basket_attributes_query = xtc_db_query("SELECT *
                                                                       FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                                                                      WHERE customers_id = '" . (int)$customerId . "'
                                                                        AND products_id = '" . xtc_db_input($customers_basket['products_id']) . "'
                                                                   ORDER BY customers_basket_attributes_id");
                while ($customers_basket_attributes = xtc_db_fetch_array($customers_basket_attributes_query)) {
                    $customers_basket['attributes'][] = $customers_basket_attributes;
                }

                $basket[] = $customers_basket;
            }
        }

        $result = $this->encode_request($basket);
        return $result;
    }

    /**
     * Read a customer basket by the given customer id.
     *
     * @param int $customerId The customer id
     *
     * @throws Exception
     *
     * @return array The customer data
     */
    public function GetCustomerWishlist(int $customerId): array
    {
        // Input validation
        if (empty($customerId)) {
            throw new Exception('Customer ID required');
        }

        $wishlist = [];
        $customer_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS_WISHLIST . "
                                           WHERE customers_id = '" . (int)$customerId . "'");
        if (xtc_db_num_rows($customer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Customer wishlist not found: %s', $customerId));
        } else {
            $customers_wishlist_query = xtc_db_query("SELECT *
                                                        FROM " . TABLE_CUSTOMERS_WISHLIST . "
                                                       WHERE customers_id = '" . (int)$customerId . "'
                                                    ORDER BY customers_basket_id");
            while ($customers_wishlist = xtc_db_fetch_array($customers_wishlist_query)) {
                $customers_wishlist['attributes'] = [];
                $customers_wishlist_attributes_query = xtc_db_query("SELECT *
                                                                       FROM " . TABLE_CUSTOMERS_WISHLIST_ATTRIBUTES . "
                                                                      WHERE customers_id = '" . (int)$customerId . "'
                                                                        AND products_id = '" . xtc_db_input($customers_wishlist['products_id']) . "'
                                                                   ORDER BY customers_basket_attributes_id");
                while ($customers_wishlist_attributes = xtc_db_fetch_array($customers_wishlist_attributes_query)) {
                    $customers_wishlist['attributes'][] = $customers_wishlist_attributes;
                }

                $wishlist[] = $customers_wishlist;
            }
        }

        $result = $this->encode_request($wishlist);
        return $result;
    }

    /**
     * Read whos online by given conditions
     *
     * @param mixed[] $options
     *
     * @return array The whos online data
     */
    public function GetWhosOnline(array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        $conditions = [];
        if ((int)$this->options['from'] > 0) {
            $conditions[] = " time_entry >= '" . (int)$this->options['from'] . "' ";
        }
        if ((int)$this->options['to'] > 0) {
            $conditions[] = " time_last_click <= '" . (int)$this->options['to'] . "' ";
        }

        $where = '';
        if (count($conditions) > 0) {
            $where = " WHERE " . implode(' AND ', $conditions);
        }

        $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM " . TABLE_WHOS_ONLINE . "
                                              " . $where);
        $count = xtc_db_fetch_array($count_query);

        if ($count['total'] < 1) {
            return $this->errormessage('no Customer found');
        }

        $data = [];
        $customers_query = xtc_db_query("SELECT *
                                             FROM " . TABLE_WHOS_ONLINE . "
                                                  " . $where . "
                                         ORDER BY time_last_click DESC
                                            LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($customers = xtc_db_fetch_array($customers_query)) {
            $data[] = $this->encode_request($customers);
        }

        $result = [
            'paging' => [
                'total' => $count['total']
            ],
            'data' => $data
        ];

        if ($count['total'] > count($data)) {
            if ($this->options['page'] > 1) {
                $result['paging']['prev'] = HTTPS_SERVER . DIR_WS_CATALOG . ltrim($this->options['path'], '/') . '?' . xtc_get_all_get_params(array('page')) . 'page=' . ($this->options['page'] - 1);
            }
            if (((($this->options['page'] - 1) * $this->options['limit']) + $this->options['limit']) < $count['total']) {
                $result['paging']['next'] = HTTPS_SERVER . DIR_WS_CATALOG . ltrim($this->options['path'], '/') . '?' . xtc_get_all_get_params(array('page')) . 'page=' . ($this->options['page'] + 1);
            }
        }

        return $result;
    }
}
