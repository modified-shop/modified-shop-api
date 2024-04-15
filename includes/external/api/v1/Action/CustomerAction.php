<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action;

  use api\v1\Action\BaseAction;
  use Exception;

  /**
   * Service.
   */
  final class CustomerAction extends BaseAction
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
      public function getCustomerDetails(int $customerId): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }
          
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
              throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
              $customer = xtc_db_fetch_array($customer_query);
              unset($customer['customers_password']);
              unset($customer['customers_password_time']);
            
              $info = array();
              $customers_info_query = xtc_db_query("SELECT *
                                                      FROM ".TABLE_CUSTOMERS_INFO."
                                                     WHERE customers_info_id = '".(int)$customerId."'");
              while ($customers_info = xtc_db_fetch_array($customers_info_query)) {
                  $info[] = $customers_info;
              }
          }
          
          $result = [
              'customers' => $this->encode_request($customer),
              'customers_info' => $info,
              'customers_memo' => $this->getCustomerMemos($customerId),
              'address_book' => $this->getCustomerAddressBooks($customerId),
          ];
          
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
      public function getCustomerAddressBooks(int $customerId): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }
          
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
              throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
              $address_book_array = array();
              $address_book_query = xtc_db_query("SELECT address_book_id
                                                    FROM ".TABLE_ADDRESS_BOOK." ab
                                                   WHERE customers_id = '".(int)$customerId."'");
              while ($address_book = xtc_db_fetch_array($address_book_query)) {
                  $address_book_array[] = $this->getCustomerAddressBook($customerId, $address_book['address_book_id']);
              }
          }

          $result = $this->encode_request($address_book_array);
          return $result;
      }

      /**
       * Read a customer address book by the given customer id and address book id.
       *
       * @param int $customerId The customer id
       * @param int $addressbookId The address book id
       *
       * @throws Exception
       *
       * @return array The address book data
       */
      public function getCustomerAddressBook(int $customerId, int $addressbookId): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }
          
          if (empty($addressbookId)) {
              throw new Exception('Addressbook ID required');
          }
          
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
              throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
              $address_book_query = xtc_db_query("SELECT ab.*,
                                                         c.countries_name,
                                                         c.countries_iso_code_2,
                                                         c.countries_iso_code_3,
                                                         z.zone_name,
                                                         z.zone_code
                                                    FROM ".TABLE_ADDRESS_BOOK." ab
                                                    JOIN ".TABLE_COUNTRIES." c
                                                         ON ab.entry_country_id = c.countries_id
                                               LEFT JOIN ".TABLE_ZONES." z
                                                         ON ab.entry_country_id = z.zone_country_id
                                                            AND ab.entry_zone_id = z.zone_id
                                                   WHERE ab.customers_id = '".(int)$customerId."'
                                                     AND ab.address_book_id = '".(int)$addressbookId."'");
              $address_book = xtc_db_fetch_array($address_book_query);
          }

          $result = $this->encode_request($address_book);
          return $result;
      }

      /**
       * Update a customer address book by the given customer id and address book id.
       *
       * @param int $customerId The customer id
       * @param int $addressbookId The address book id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The address book data
       */
      public function updateCustomerAddressBook(int $customerId, int $addressbookId, array $options): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }
          
          if (empty($addressbookId)) {
              throw new Exception('Addressbook ID required');
          }
          
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
              throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
              $address_book_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_ADDRESS_BOOK."
                                                   WHERE customers_id = '".(int)$customerId."'
                                                     AND address_book_id = '".(int)$addressbookId."'");
              if (xtc_db_num_rows($address_book_query) < 1) {
                  throw new Exception(sprintf('Address book not found: %s', $addressbookId));
              } else {
                  $address_book = xtc_db_fetch_array($address_book_query);
            
                  foreach ($address_book as $key => $value) {
                      if (isset($this->options[TABLE_ADDRESS_BOOK][$key])) {
                          $address_book[$key] = $this->options[TABLE_ADDRESS_BOOK][$key];
                      }
                  }
              
                  // Input validation
                  $this->checkTableData(TABLE_ADDRESS_BOOK, $address_book);
                  xtc_db_perform(TABLE_ADDRESS_BOOK, $address_book, 'update', "customers_id = '".(int)$customerId."' AND address_book_id = '".(int)$addressbookId."'");
              }
          }

          return $this->getCustomerAddressBook($customerId, $addressbookId);
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
      public function getCustomerMemos(int $customerId): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }
          
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
            throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
            $customers_memo_array = array();
            $customers_memo_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_CUSTOMERS_MEMO."
                                                   WHERE customers_id = '".(int)$customerId."'");
            while ($customers_memo = xtc_db_fetch_array($customers_memo_query)) {
              $customers_memo_array[] = $customers_memo;
            }
          }

          $result = $this->encode_request($customers_memo_array);
          return $result;
      }

      /**
       * Read customers by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The customer data
       */
      public function getCustomers($options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
          
          $conditions = [];
          if (!empty(preg_replace('/[^\d\,]/', '', $this->options['status']))) {
              $conditions[] = " customers_status IN (".preg_replace('/[^\d\,]/', '', $this->options['status']).") ";
          }
          if ((int)$this->options['from'] > 0) {
              $conditions[] = " customers_date_added >= '".date('Y-m-d H:i:s', (int)$this->options['from'])."' ";
          }
          if ((int)$this->options['to'] > 0) {
              $conditions[] = " customers_date_added <= '".date('Y-m-d H:i:s', (int)$this->options['to'])."' ";
          }
          
          if (count($conditions) > 0) {
            $where = " WHERE ".implode(' AND ', $conditions);
          }
          
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_CUSTOMERS."
                                              ".$where);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Customer found');
          }
          
          $data = [];
          $customers_query = xtc_db_query("SELECT customers_id
                                             FROM ".TABLE_CUSTOMERS."
                                                  ".$where."
                                         ORDER BY customers_date_added DESC
                                            LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($customers = xtc_db_fetch_array($customers_query)) {
              $data[] = $this->getCustomerDetails($customers['customers_id']);
          }
          
          $result = [
              'paging' => [
                  'total' => $count['total']
              ],
              'data' => $data
          ];
          
          if ($count['total'] > count($data)) {
              if ($this->options['page'] > 1) {
                  $result['paging']['prev'] = HTTPS_SERVER.DIR_WS_CATALOG.ltrim($this->options['path'], '/').'?'.xtc_get_all_get_params(array('page')).'page='.($this->options['page'] - 1);
              }
              if (((($this->options['page'] - 1) * $this->options['limit']) + $this->options['limit']) < $count['total']) {
                  $result['paging']['next'] = HTTPS_SERVER.DIR_WS_CATALOG.ltrim($this->options['path'], '/').'?'.xtc_get_all_get_params(array('page')).'page='.($this->options['page'] + 1);
              }
          }
          
          return $result;
      }
      
      /**
       * Delete an customer by the given customer id.
       *
       * @param int $customerId The customer id
       *
       * @throws Exception
       *
       * @return void
       */
      public function deleteCustomer(int $customerId): void
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }

          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
            throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_ADDRESS_BOOK." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_INFO." WHERE customers_info_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_IP." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET_ATTRIBUTES." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_WISHLIST." WHERE customers_id = '".(int)$customerId."'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_WISHLIST_ATTRIBUTES." WHERE customers_id = '".(int)$customerId."'");
          }
          
          $this->logger->info(sprintf('Customer deleted successfully: %s', $customerId));
      }
  }
