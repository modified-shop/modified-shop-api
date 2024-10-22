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
      use CustomerGetAction;
      use CustomerDeleteAction;

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
      
  }
