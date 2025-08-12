<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Newsletter;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  final class NewsletterAction extends BaseAction
  {
      use NewsletterGetAction;
      use NewsletterDeleteAction;
      
      /**
       * Insert an newsletter by the given options.
       *
       * @param mixed[] $options
       *
       * @return array The newsletter data
       */
      public function InsertNewsletterRecipients(array $options): array
      {
          $newsletter = $this->InsertUpdateNewsletterRecipients(0, $options);
          
          return $newsletter;
      }

      /**
       * Insert or Update an newsletter by the given newsletter id.
       *
       * @param int $newsletterId The newsletter id
       * @param mixed[] $options
       *
       * @return array The newsletter data
       */
      public function InsertUpdateNewsletterRecipients(int $newsletterId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if ($newsletterId > 0) {
              $action = 'update';
              $newsletter_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                                 WHERE mail_id = '".(int)$newsletterId."'");
              if (xtc_db_num_rows($newsletter_query) < 1) {
                  $this->errormessage(sprintf('Newsletter not found: %s', $newsletterId));
              } else {
                  $newsletter = xtc_db_fetch_array($newsletter_query);
              }
          } else {
              $action = 'insert';
              $newsletter = $this->getDefaultTableValues(TABLE_NEWSLETTER_RECIPIENTS);
              $newsletter['date_added'] = 'now()';
          }

          foreach ($newsletter as $key => $value) {
              if (isset($this->options[$key])) {
                  $newsletter[$key] = $this->options[$key];
              }
          }

          if ($action == 'insert') {
              $check_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                            WHERE customers_email_address = '".xtc_db_input($newsletter['customers_email_address'])."'");
              if (xtc_db_num_rows($check_query) > 0) {
                  $this->errormessage('Newsletter Email Address already exists', 400);
              }
          }
          
          // Input validation
          $this->checkTableData(TABLE_NEWSLETTER_RECIPIENTS, $newsletter);
          unset($newsletter['mail_id']);

          xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS, $newsletter, $action, "mail_id = '".(int)$newsletterId."'");
          if ($action == 'insert') {
              $newsletterId = xtc_db_insert_id();
          }

          return $this->GetSingleNewsletterRecipients($newsletterId);
      }

  }
