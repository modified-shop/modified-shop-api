<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Customer;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;

  /**
   * Service.
   */
  final class CustomerAction extends BaseAction
  {
      use CustomerGetAction;
      use CustomerDeleteAction;

      /**
       * Insert a customer by the given options.
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The customer data
       */
      public function InsertCustomer(array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if (isset($this->options[TABLE_CUSTOMERS])) {
              $customers = $this->InsertUpdateCustomer(0, $this->options[TABLE_CUSTOMERS]);
              $customerId = $customers['customers_id'];
          }

          if (!isset($customerId)) {
              throw new Exception('Customer ID required');
          } else {
              if (isset($this->options[TABLE_CUSTOMERS_INFO])) {
                  $customers_info = $this->InsertUpdateInfo($customerId, $this->options[TABLE_CUSTOMERS_INFO]);
              }
              if (isset($this->options[TABLE_ADDRESS_BOOK])) {
                  $address_book = $this->InsertUpdateAddressBook($customerId, $this->options[TABLE_ADDRESS_BOOK]);
              }
          }

          return $this->GetCustomerDetails($customerId);
      }

      /**
       * Insert or Update a customer by the given customer id.
       *
       * @param int $customerId The customer id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The customer data
       */
      public function InsertUpdateCustomer(int $customerId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if ($customerId > 0) {
              $action = 'update';
              $customer_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_CUSTOMERS."
                                               WHERE customers_id = '".(int)$customerId."'");
              if (xtc_db_num_rows($customer_query) < 1) {
                  throw new Exception(sprintf('Customer not found: %s', $customerId));
              } else {
                  $customer = xtc_db_fetch_array($customer_query);
                  $customer['customers_last_modified'] = 'now()';
              }
          } else {
              $action = 'insert';
              $customer = $this->getDefaultTableValues(TABLE_CUSTOMERS);
              $customer['customers_date_added'] = 'now()';
          }

          foreach ($customer as $key => $value) {
              if (isset($this->options[$key])) {
                  $customer[$key] = $this->options[$key];
              }
          }

          // Input validation
          $this->checkTableData(TABLE_CUSTOMERS, $customer);
          unset($customer['customers_id']);

          xtc_db_perform(TABLE_CUSTOMERS, $customer, $action, "customers_id = '".(int)$customerId."'");
          if ($action == 'insert') {
              $customerId = xtc_db_insert_id();
          }

          return $this->GetCustomer($customerId);
      }

      /**
       * Insert or Update a customer info by the given customer id.
       *
       * @param int $customerId The customer id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The customer data
       */
      public function InsertUpdateInfo(int $customerId, array $options): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
              throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
              $customers_info_query = xtc_db_query("SELECT *
                                                      FROM ".TABLE_CUSTOMERS_INFO."
                                                     WHERE customers_info_id = '".(int)$customerId."'");
              if (xtc_db_num_rows($customers_info_query) > 0) {
                  $action = 'update';
                  $customers_info = xtc_db_fetch_array($customers_info_query);
              } else {
                  $action = 'insert';
                  $customers_info = $this->getDefaultTableValues(TABLE_CUSTOMERS_INFO);
                  $customers_info['customers_info_id'] = (int)$customerId;
              }

              foreach ($customers_info as $key => $value) {
                  if (isset($this->options[$key])) {
                      $customers_info[$key] = $this->options[$key];
                  }
              }

              // Input validation
              $this->checkTableData(TABLE_CUSTOMERS_INFO, $customers_info);
              xtc_db_perform(TABLE_CUSTOMERS_INFO, $customers_info, $action, "customers_info_id = '".(int)$customerId."'");
          }

          return $this->GetCustomerInfo($customerId);
      }

      /**
       * Insert or Update a customer memo by the given customer id.
       *
       * @param int $customerId The customer id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The customer data
       */
      public function InsertUpdateMemo(int $customerId, array $options): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
              throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
              $where = '';
              if (isset($this->options['memo_id'])) {
                  $where = "AND memo_id = '".(int)$this->options['memo_id']."'";
                  $customers_memo_query = xtc_db_query("SELECT *
                                                          FROM ".TABLE_CUSTOMERS_MEMO."
                                                         WHERE customers_id = '".(int)$customerId."'
                                                               ".$where);
                  if (xtc_db_num_rows($customers_memo_query) < 1) {
                      throw new Exception(sprintf('Memo ID invalid'));
                  } else {
                      $action = 'update';
                      $customers_memo = xtc_db_fetch_array($customers_memo_query);
                  }
              } else {
                  $action = 'insert';
                  $customers_memo = $this->getDefaultTableValues(TABLE_CUSTOMERS_MEMO);
                  $customers_memo['customers_id'] = (int)$customerId;
                  $customers_memo['memo_date'] = 'now()';
              }

              foreach ($customers_memo as $key => $value) {
                  if (isset($this->options[$key])) {
                      $customers_memo[$key] = $this->options[$key];
                  }
              }

              // Input validation
              $this->checkTableData(TABLE_CUSTOMERS_MEMO, $customers_memo);
              xtc_db_perform(TABLE_CUSTOMERS_MEMO, $customers_memo, $action, "customers_id = '".(int)$customerId."' ".$where);
          }

          return $this->GetCustomerMemos($customerId);
      }

      /**
       * Insert or Update a customer address book by the given customer id.
       *
       * @param int $customerId The customer id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The address book data
       */
      public function InsertUpdateAddressBook(int $customerId, array $options): array
      {
          // Input validation
          if (empty($customerId)) {
              throw new Exception('Customer ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          $customer_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".(int)$customerId."'");
          if (xtc_db_num_rows($customer_query) < 1) {
              throw new Exception(sprintf('Customer not found: %s', $customerId));
          } else {
              $where = '';
              if (isset($this->options['address_book_id'])) {
                  $where = "AND address_book_id = '".(int)$this->options['address_book_id']."'";
                  $address_book_query = xtc_db_query("SELECT *
                                                        FROM ".TABLE_ADDRESS_BOOK."
                                                       WHERE customers_id = '".(int)$customerId."'
                                                             ".$where);
                  if (xtc_db_num_rows($address_book_query) < 1) {
                      throw new Exception(sprintf('Address book ID invalid'));
                  } else {
                      $action = 'update';
                      $address_book = xtc_db_fetch_array($address_book_query);
                      $address_book['address_last_modified'] = 'now()';
                  }
              } else {
                  $action = 'insert';
                  $address_book = $this->getDefaultTableValues(TABLE_ADDRESS_BOOK);
                  $address_book['customers_id'] = (int)$customerId;
                  $address_book['address_date_added'] = 'now()';
              }

              foreach ($address_book as $key => $value) {
                  if (isset($this->options[$key])) {
                      $address_book[$key] = $this->options[$key];
                  }
              }

              // Input validation
              $this->checkTableData(TABLE_ADDRESS_BOOK, $address_book);
              xtc_db_perform(TABLE_ADDRESS_BOOK, $address_book, $action, "customers_id = '".(int)$customerId."' ".$where);
          }

          return $this->GetCustomerAddressBooks($customerId);
      }
      
  }
