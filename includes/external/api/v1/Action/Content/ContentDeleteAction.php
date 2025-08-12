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
  trait ContentDeleteAction
  {
      /**
       * Delete a content by the given content group id.
       *
       * @param int $contentGroupId The content group id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteContent(int $contentGroupId): void
      {
          // Input validation
          if (empty($contentGroupId)) {
              throw new Exception('Content Group ID required');
          }

          $content_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CONTENT_MANAGER."
                                           WHERE content_group = '".(int)$contentGroupId."'");
          if (xtc_db_num_rows($content_query) < 1) {
            $this->errormessage(sprintf('Content Group not found: %s', $contentGroupId));
          } else {
              // disable Exception
              $this->throw_exception = false;

              //delete details
              $this->DeleteAllContentContent($contentGroupId);

              //delete
              xtc_db_query("DELETE FROM ".TABLE_CONTENT_MANAGER." WHERE content_group = '".(int)$contentGroupId."'");
          }
          
          $this->logger->info(sprintf('Content Group deleted successfully: %s', $contentGroupId));
      }

      /**
       * Delete a content by the given product id and content group id.
       *
       * @param int $$contentGroupId The content group id
       * @param int $contentId The content id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteContentContent(int $contentGroupId, int $contentId): void
      {
          // Input validation
          if (empty($contentGroupId)) {
              throw new Exception('Content Group ID required');
          }

          $where = '';
          if ($contentId > 0) {
              $where = "AND content_id = '".(int)$contentId."'";
          }

          $content_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_CONTENT_MANAGER_CONTENT."
                                          WHERE content_manager_id = '".(int)$contentGroupId."'
                                                ".$where);
          if (xtc_db_num_rows($content_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Content Group not found: %s', $contentGroupId));
          } else {
              while ($content = xtc_db_fetch_array($content_query)) {
                  $duplicate_content_query = xtc_db_query("SELECT COUNT(*) AS total 
                                                             FROM ".TABLE_CONTENT_MANAGER_CONTENT." 
                                                            WHERE content_file = '".xtc_db_input($content['content_file'])."' 
                                                              AND content_manager_id != '".(int)$contentGroupId."'");
                  $duplicate_content = xtc_db_fetch_array($duplicate_content_query);
                  if ($duplicate_content['total'] == 0
                      && is_file(DIR_FS_CATALOG.'media/content/'.$content['content_file'])
                      )
                  {
                     unlink(DIR_FS_CATALOG.'media/content/'.$content['content_file']);
                  }

                  xtc_db_query("DELETE FROM ".TABLE_CONTENT_MANAGER_CONTENT." 
                                      WHERE content_manager_id = '".(int)$contentGroupId."'
                                        AND content_id = '".(int)$content['content_id']."'");
              }
          }
      }

      /**
       * Delete all contents by the given content group id.
       *
       * @param int $contentId The content group id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllContentContent(int $contentGroupId): void
      {
          // Input validation
          if (empty($contentGroupId)) {
              throw new Exception('Content Group ID required');
          }

          $this->DeleteContentContent($contentGroupId, 0);
      }

  }
