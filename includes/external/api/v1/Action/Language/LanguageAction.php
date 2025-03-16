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
  final class LanguageAction extends BaseAction
  {
      use LanguageGetAction;
      use LanguageDeleteAction;
      
      /**
       * Insert an language by the given options.
       *
       * @param mixed[] $options
       *
       * @return array The language data
       */
      public function InsertLanguage(array $options): array
      {
          $language = $this->InsertUpdateLanguage(0, $options);
          
          return $language;
      }

      /**
       * Insert or Update an language by the given language id.
       *
       * @param int $languageId The language id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The language data
       */
      public function InsertUpdateLanguage(int $languageId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if ($languageId > 0) {
              $action = 'update';
              $language_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_LANGUAGES."
                                               WHERE languages_id = '".(int)$languageId."'");
              if (xtc_db_num_rows($language_query) < 1) {
                  throw new Exception(sprintf('Language not found: %s', $languageId));
              } else {
                  $language = xtc_db_fetch_array($language_query);
              }
          } else {
              $action = 'insert';
              $language = $this->getDefaultTableValues(TABLE_LANGUAGES);
          }

          foreach ($language as $key => $value) {
              if (isset($this->options[$key])) {
                  $language[$key] = $this->options[$key];
              }
          }

          if ($action == 'insert') {
              $check_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_LANGUAGES."
                                            WHERE code = '".xtc_db_input($language['code'])."'");
              if (xtc_db_num_rows($check_query) > 0) {
                  throw new Exception('Language Code already exists');
              }
          }
          
          // Input validation
          $this->checkTableData(TABLE_LANGUAGES, $language);
          unset($language['languages_id']);

          xtc_db_perform(TABLE_LANGUAGES, $language, $action, "languages_id = '".(int)$languageId."'");
          if ($action == 'insert') {
              $languageId = xtc_db_insert_id();
          }

          return $this->getSingleLanguage($languageId);
      }

  }
