<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Language;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  trait LanguageDeleteAction
  {
      /**
       * Delete a language by the given language id.
       *
       * @param int $languageId The currency id
       *
       * @return void
       */
      public function DeleteLanguage(int $languageId): void
      {
          // Input validation
          if (empty($languageId)) {
              throw new Exception('Language ID required');
          }

          $language_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_LANGUAGES."
                                           WHERE languages_id = '".(int)$languageId."'");
          if (xtc_db_num_rows($language_query) < 1) {
              throw new Exception(sprintf('Language not found: %s', $languageId));
          } else {
              //delete
              xtc_db_query("DELETE FROM ".TABLE_LANGUAGES." WHERE languages_id = '".(int)$languageId."'");
          }
          
          $this->logger->info(sprintf('Language deleted successfully: %s', $languageId));
      }

  }
