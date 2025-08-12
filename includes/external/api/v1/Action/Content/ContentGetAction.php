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
  trait ContentGetAction
  {
      /**
       * Read a content by the given content id.
       *
       * @param int $contentGroupId The content id
       *
       * @throws Exception
       *
       * @return array The content data
       */
      public function GetContentDetails(int $contentGroupId): array
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

              $result = [
                  'content_manager' => $this->GetContent($contentGroupId),
              ];         
              
              if (isset($this->options['with'])) {
                  $with = explode(',', $this->options['with']);
                  if (in_array('content', $with) !== false) {
                      $result['content_manager_content'] = $this->GetContentContent($contentGroupId);
                  }
              }
              
              return $result;
          }
      }

      /**
       * Read a content by the given content id.
       *
       * @param int $contentGroupId The content id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The content data
       */
      public function GetSingleContent(int $contentGroupId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          // Input validation
          if (empty($contentGroupId)) {
              throw new Exception('Content Group ID required');
          }

          $result = $this->GetContentDetails($contentGroupId);
          return $result;
      }

      /**
       * Read content by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The content data
       */
      public function GetContents(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;

          $conditions = [];
          if (isset($this->options['status']) && preg_replace('/[^\d\,]/', '', $this->options['status']) != '') {
              $conditions[] = " content_status IN (".preg_replace('/[^\d\,]/', '', $this->options['status']).") ";
          }
          if (isset($this->options['flag']) && preg_replace('/[^\d\,]/', '', $this->options['flag']) != '') {
              $conditions[] = " file_flag IN (".preg_replace('/[^\d\,]/', '', $this->options['flag']).") ";
          }
          if ((int)$this->options['from'] > 0) {
              $conditions[] = " date_added >= '".date('Y-m-d H:i:s', (int)$this->options['from'])."' ";
          }
          if ((int)$this->options['to'] > 0) {
              $conditions[] = " date_added <= '".date('Y-m-d H:i:s', (int)$this->options['to'])."' ";
          }
          
          $where = '';
          if (count($conditions) > 0) {
            $where = " WHERE ".implode(' AND ', $conditions);
          }
                                                        
          $count_query = xtc_db_query("SELECT count(DISTINCT content_group) as total
                                         FROM ".TABLE_CONTENT_MANAGER."
                                              ".$where);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              $this->errormessage('no Content found');
          }
          
          $data = [];
          $content_query = xtc_db_query("SELECT content_group
                                           FROM ".TABLE_CONTENT_MANAGER."
                                                ".$where."
                                       GROUP BY content_group
                                       ORDER BY content_group ASC
                                          LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($content = xtc_db_fetch_array($content_query)) {
              $data[] = $this->GetContentDetails($content['content_group']);
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

      /**
       * Read a content by the given content id.
       *
       * @param int $contentGroupId The content id
       *
       * @throws Exception
       *
       * @return array The content data
       */
      public function GetContent(int $contentGroupId): array
      {
          // Input validation
          if (empty($contentGroupId)) {
              throw new Exception('Content Group ID required');
          }
          
          $content_query = xtc_db_query("SELECT cm.*,
                                                l.code
                                           FROM ".TABLE_CONTENT_MANAGER." cm
                                           JOIN ".TABLE_LANGUAGES." l
                                                ON l.languages_id = cm.languages_id
                                          WHERE cm.content_group = '".(int)$contentGroupId."'");
          if (xtc_db_num_rows($content_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Content Group not found: %s', $contentGroupId));
          } else {
              $data = [];
              while ($content = xtc_db_fetch_array($content_query)) {
                  $code = $content['code'];
                  unset($content['code']);

                  $data[$code] = $content;
             }
          }
          
          $result = $this->encode_request($data);
          return $result;
      }
    
      /**
       * Read a content content by the given content id.
       *
       * @param int $contentGroupId The content id
       *
       * @throws Exception
       *
       * @return array The content data
       */
      public function GetContentContent(int $contentGroupId): array
      {
          // Input validation
          if (empty($contentGroupId)) {
              throw new Exception('Content Group ID required');
          }

          $content_data = [];
          $content_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_CONTENT_MANAGER_CONTENT."
                                          WHERE content_manager_id = '".(int)$contentGroupId."'");
          if (xtc_db_num_rows($content_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Content Group not found: %s', $contentGroupId));
          } else {
              $data = [];
              $content_query = xtc_db_query("SELECT cmc.*,
                                                    l.code
                                               FROM ".TABLE_CONTENT_MANAGER_CONTENT." cmc
                                               JOIN ".TABLE_LANGUAGES." l
                                                    ON l.languages_id = cmc.languages_id
                                              WHERE cmc.content_manager_id = '".(int)$contentGroupId."'
                                           ORDER BY cmc.sort_order, cmc.content_id");
              while ($content = xtc_db_fetch_array($content_query)) {
                  $code = $content['code'];
                  unset($content['code']);

                  $data[$code] = $content;
              }
              $content_data[] = $data;
          }

          $result = $this->encode_request($content_data);
          return $result;
      }

      /**
       * Read file flag by given conditions
       *
       * @param mixed[] $options
       *
       * @return array The file flag data
       */
      public function GetContentFileFlag(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                                                        
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_CM_FILE_FLAGS);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              $this->errormessage('no File Flag found');
          }
          
          $data = [];
          $file_flag_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_CM_FILE_FLAGS."
                                            LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($file_flag = xtc_db_fetch_array($file_flag_query)) {
              $data[] = $this->encode_request($file_flag);
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
