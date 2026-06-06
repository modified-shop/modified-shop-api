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
                  return $this->errormessage(sprintf('Customer not found: %s', $customerId));
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
              return $this->errormessage(sprintf('Customer not found: %s', $customerId));
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
       * Insert an customer memo by given customer id.
       *
       * @param int $customerId The customer id
       * @param mixed[] $options
       *
       * @return array The order tracking data
       */
      public function InsertMemo(int $customerId, array $options): array
      {
          $customers_memo = $this->InsertUpdateMemo($customerId, 0, $options);
          
          return $customers_memo;
      }

      /**
       * Insert or Update a customer memo by the given customer id.
       *
       * @param int $customerId The customer id
       * @param int $memoId The memo id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The customer data
       */
      public function InsertUpdateMemo(int $customerId, int $memoId, array $options): array
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
              return $this->errormessage(sprintf('Customer not found: %s', $customerId));
          } else {
              if ($memoId > 0) {     
                  $action = 'update';
                  $customers_memo_query = xtc_db_query("SELECT *
                                                          FROM ".TABLE_CUSTOMERS_MEMO."
                                                         WHERE customers_id = '".(int)$customerId."'
                                                           AND memo_id = '".(int)$memoId."'");
                  if (xtc_db_num_rows($customers_memo_query) < 1) {
                      return $this->errormessage(sprintf('Customer Memo Id not found: %s', $memoId));
                  } else {
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
       * Insert an customer address book by given customer id.
       *
       * @param int $customerId The customer id
       * @param mixed[] $options
       *
       * @return array The order tracking data
       */
      public function InsertAddressBook(int $customerId, array $options): array
      {
          $address_book = $this->InsertUpdateAddressBook($customerId, 0, $options);
          
          return $address_book;
      }

      /**
       * Insert or Update a customer address book by the given customer id.
       *
       * @param int $customerId The customer id
       * @param int $addressBookId The customer id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The address book data
       */
      public function InsertUpdateAddressBook(int $customerId, int $addressBookId, array $options): array
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
              return $this->errormessage(sprintf('Customer not found: %s', $customerId));
          } else {
              $where = '';
              if ($addressBookId > 0) {     
                  $address_book_query = xtc_db_query("SELECT *
                                                        FROM ".TABLE_ADDRESS_BOOK."
                                                       WHERE customers_id = '".(int)$customerId."'
                                                         AND address_book_id = '".(int)$addressBookId."'");
                  if (xtc_db_num_rows($address_book_query) < 1) {
                      return $this->errormessage(sprintf('Address book Id not found: %s', $addressBookId));
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
