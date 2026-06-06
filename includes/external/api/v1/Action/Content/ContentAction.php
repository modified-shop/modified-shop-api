<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Content;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  final class ContentAction extends BaseAction
  {
      use ContentGetAction;
      use ContentDeleteAction;
      
      /**
       * Insert an content by the given options.
       *
       * @param mixed[] $options
       *
       * @return array The content data
       */
      public function InsertContent(array $options): array
      {
          $content = $this->InsertUpdateContent(0, $options);
          
          return $content;
      }

      /**
       * Insert or Update an content by the given content group id.
       *
       * @param int $contentGroupId The content group id
       * @param mixed[] $options
       *
       * @return array The content data
       */
      public function InsertUpdateContent(int $contentGroupId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if ($contentGroupId == 0) {
              $content_query = xtc_db_query("SELECT MAX(content_group) AS content_group 
                                               FROM ".TABLE_CONTENT_MANAGER);
              $content_data = xtc_db_fetch_row($content_query);
              $contentGroupId = $content_data[0] + 1;
          }

          $languages_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_LANGUAGES);
          while ($languages = xtc_db_fetch_array($languages_query)) { 
              $content_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_CONTENT_MANAGER."
                                               WHERE content_group = '".(int)$contentGroupId."'
                                                 AND languages_id = '".(int)$languages['languages_id']."'");
              if (xtc_db_num_rows($content_query) > 0) {
                  $content = xtc_db_fetch_array($content_query);

                  foreach ($content as $key => $value) {
                      if (isset($this->options[$languages['code']][$key])) {
                          $content[$key] = $this->options[$languages['code']][$key];
                      }
                  }

                  // Input validation
                  $this->checkTableData(TABLE_CONTENT_MANAGER, $content);
                  xtc_db_perform(TABLE_CONTENT_MANAGER, $content, 'update', "content_group = '".(int)$contentGroupId."' AND languages_id = '".(int)$languages['languages_id']."'");
              } elseif (isset($this->options[$languages['code']])) {
                  $content = $this->getDefaultTableValues(TABLE_CONTENT_MANAGER);
                  $content['content_group'] = (int)$contentGroupId;
                  $content['languages_id'] = (int)$languages['languages_id'];
            
                  foreach ($content as $key => $value) {
                      if (isset($this->options[$languages['code']][$key])) {
                          $content[$key] = $this->options[$languages['code']][$key];
                      }
                  }

                  // Input validation
                  $this->checkTableData(TABLE_CONTENT_MANAGER, $content);
                  xtc_db_perform(TABLE_CONTENT_MANAGER, $content);
              }         
          }

          return $this->GetContent($contentGroupId);
      }

      /**
       * Insert or Update a content content by the given content group id.
       *
       * @param int $$contentGroupId The content group id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The content data
       */
      public function InsertUpdateContentContent(int $contentGroupId, array $options): array
      {
          // Input validation
          if (empty($contentGroupId)) {
              throw new Exception('Content Group ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $products_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CONTENT_MANAGER."
                                           WHERE content_group = '".(int)$contentGroupId."'");
          if (xtc_db_num_rows($products_query) < 1) {
              return $this->errormessage(sprintf('Content not found: %s', $productId));
          } else {
              $languages_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_LANGUAGES);
              while ($languages = xtc_db_fetch_array($languages_query)) {
                  $where = '';
                  if (isset($this->options[$languages['code']]['content_id'])) {
                      $contentId = (int)$this->options[$languages['code']]['content_id'];
                      $where = "AND content_id = '".(int)$contentId."'";
                      $content_query = xtc_db_query("SELECT *
                                                       FROM ".TABLE_CONTENT_MANAGER_CONTENT."
                                                      WHERE content_manager_id = '".(int)$contentGroupId."'
                                                            ".$where);
                      if (xtc_db_num_rows($content_query) < 1) {
                          return $this->errormessage(sprintf('Content ID invalid'), 400);
                      } else {
                          $action = 'update';
                          $content = xtc_db_fetch_array($content_query);
                      }
                  } else {
                      $action = 'insert';
                      $content = $this->getDefaultTableValues(TABLE_CONTENT_MANAGER_CONTENT);
                      $content['content_manager_id'] = (int)$contentGroupId;
                      $content['languages_id'] = (int)$languages['languages_id'];
                  }

                  foreach ($content as $key => $value) {
                      if (isset($this->options[$languages['code']][$key])) {
                          $content[$key] = $this->options[$languages['code']][$key];
                      }
                  }

                  // Input validation
                  $this->checkTableData(TABLE_CONTENT_MANAGER_CONTENT, $content);
                  xtc_db_perform(TABLE_CONTENT_MANAGER_CONTENT, $content, $action, "content_manager_id = '".(int)$contentGroupId."' ".$where);
                  
                  if (!isset($contentId)) {
                      $contentId = xtc_db_insert_id();
                  }
                  
                  if ($content_file = xtc_try_upload(array($languages['code'] => 'content_file'), DIR_FS_CATALOG.'media/content/', '777', array_merge($this->accepted_image_extensions, $this->accepted_file_extensions, $this->accepted_extfile_extensions, $this->accepted_audio_extensions, $this->accepted_movie_extensions, $this->accepted_compressed_extensions), array_merge($this->accepted_image_mime_types, $this->accepted_file_mime_types, $this->accepted_extfile_mime_types, $this->accepted_audio_mime_types, $this->accepted_movie_mime_types, $this->accepted_compressed_mime_types))) {
                      $content_file_name = preg_replace('/[^\d\w\-\_\.]/', '', $content_file->filename);
                      
                      rename(DIR_FS_CATALOG.'media/content/'.$content_file->filename, DIR_FS_CATALOG.'media/content/'.$content_file_name);
                      copy(DIR_FS_CATALOG.'media/content/'.$content_file_name, DIR_FS_CATALOG.'media/content/backup/'.$content_file_name);
    
                      //content chmod
                      chmod(DIR_FS_CATALOG.'media/content/'.$content_file_name, 0644);

                      xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER_CONTENT."
                                       SET content_file = '".xtc_db_input($content_file_name)."'
                                     WHERE content_id = '".(int)$contentId."'");
                  }
              }          
          }

          return $this->GetContentContent($contentGroupId);
      }

  }
