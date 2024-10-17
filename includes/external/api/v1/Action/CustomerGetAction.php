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
              // disable Excetion
              $this->Excetion = false;
              
              $result = [
                  'customers' => $this->getCustomer($customerId),
              ];
    
              $with = explode(',', $this->options['with']);
              if (in_array('info', $with) !== false) {
                  $result['customers_info'] = $this->getCustomerInfo($customerId);
              }
              if (in_array('ip', $with) !== false) {
                  $result['customers_ip'] = $this->getCustomerIp($customerId);
              }
              if (in_array('memo', $with) !== false) {
                  $result['customers_memo'] = $this->getCustomerMemos($customerId);
              }
              if (in_array('address', $with) !== false) {
                  $result['address_book'] = $this->getCustomerAddressBooks($customerId);
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
      public function GetSingleCustomer(int $customerId, $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }

          $result = $this->getCustomerDetails($customerId);
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
       * Read a customer by the given customer id.
       *
       * @param int $customerId The customer id
       *
       * @throws Exception
       *
       * @return array The customer data
       */
      public function getCustomer(int $customerId): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }

          $customer = [];
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1 && $this->Excetion === true) {
              throw new Exception(sprintf('Customer not found: %s', $customerId));
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
      public function getCustomerInfo(int $customerId): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }

          $info = [];
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS_INFO."
                                           WHERE customers_info_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1 && $this->Excetion === true) {
              throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
              $info = xtc_db_fetch_array($customers_info_query);
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
      public function getCustomerIp(int $customerId): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }

          $ip = [];
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS_IP."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1 && $this->Excetion === true) {
              throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
              $customers_ip_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_CUSTOMERS_IP."
                                                   WHERE customers_id = '".(int)$customerId."'");
              while ($customers_ip = xtc_db_fetch_array($customers_ip_query)) {
                  $ip[] = $customers_ip;
              }
          }

          $result = $this->encode_request($ip);
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
          
          $address_book_array = [];
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
              throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
              $address_book_query = xtc_db_query("SELECT address_book_id
                                                    FROM ".TABLE_ADDRESS_BOOK." ab
                                                   WHERE customers_id = '".(int)$customerId."'
                                                ORDER BY address_book_id ASC");
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
       * @param int $addressBookId The address book id
       *
       * @throws Exception
       *
       * @return array The address book data
       */
      public function getCustomerAddressBook(int $customerId, int $addressBookId): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }
          
          if (empty($addressBookId)) {
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
                                                     AND ab.address_book_id = '".(int)$addressBookId."'");
              $address_book = xtc_db_fetch_array($address_book_query);
          }

          $result = $this->encode_request($address_book);
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
      public function getCustomerMemos(int $customerId): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }
          
          $memo = [];
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
            throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
            $customers_memo_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_CUSTOMERS_MEMO."
                                                   WHERE customers_id = '".(int)$customerId."'
                                                ORDER BY memo_id ASC");
            while ($customers_memo = xtc_db_fetch_array($customers_memo_query)) {
              $memo[] = $customers_memo;
            }
          }

          $result = $this->encode_request($memo);
          return $result;
      }
      
  }
