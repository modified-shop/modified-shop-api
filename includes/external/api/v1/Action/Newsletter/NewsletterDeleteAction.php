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
  trait NewsletterDeleteAction
  {
      /**
       * Delete a newsletter by the given newsletter id.
       *
       * @param int $newsletterId The currency id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteNewsletterRecipients(int $newsletterId): void
      {
          // Input validation
          if (empty($newsletterId)) {
              throw new Exception('Newsletter ID required');
          }

          $newsletter_query = xtc_db_query("SELECT *
                                              FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                             WHERE mail_id = '".(int)$newsletterId."'");
          if (xtc_db_num_rows($newsletter_query) < 1) {
              return $this->errormessage(sprintf('Newsletter not found: %s', $newsletterId));
          } else {
              //delete
              xtc_db_query("DELETE FROM ".TABLE_NEWSLETTER_RECIPIENTS." WHERE mail_id = '".(int)$newsletterId."'");
          }
          
          $this->logger->info(sprintf('Newsletter deleted successfully: %s', $newsletterId));
      }

  }
