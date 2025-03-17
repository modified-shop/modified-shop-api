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
  trait LanguageGetAction
  {
      /**
       * Read a language by the given language id.
       *
       * @param int $languageId The language id
       *
       * @throws Exception
       *
       * @return array The language data
       */
      public function GetSingleLanguage(int $languageId): array
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
              $language = xtc_db_fetch_array($language_query);
          }
          
          $result = $this->encode_request($language);
          return $result;
      }

      /**
       * Read languages by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The languages data
       */
      public function GetLanguages(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                                                        
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_LANGUAGES);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Language found');
          }
          
          $data = [];
          $languages_query = xtc_db_query("SELECT languages_id
                                             FROM ".TABLE_LANGUAGES."
                                         ORDER BY languages_id ASC
                                            LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($languages = xtc_db_fetch_array($languages_query)) {
              $data[] = $this->GetSingleLanguage($languages['languages_id']);
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

  }
