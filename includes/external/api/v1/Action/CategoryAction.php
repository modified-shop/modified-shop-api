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
  final class CategoryAction extends BaseAction
  {
      /**
       * @var mixed[]
       */
      protected $images_type_array = [
        '',
        '_list',
        '_mobile'
      ];

      /**
       * Read a category by the given category id.
       *
       * @param int $categoryId The category id
       *
       * @throws Exception
       *
       * @return array The category data
       */
      public function GetSingleCategory(int $categoryId): array
      {
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }
                    
          $result = [
              'categories' => $this->getCategory($categoryId),
              'categories_description' => $this->getCategoryDescription($categoryId),
          ];
          
          return $result;
      }

      /**
       * Read a category by the given category id.
       *
       * @param int $categoryId The category id
       *
       * @throws Exception
       *
       * @return array The category data
       */
      public function GetCategory(int $categoryId): array
      {
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }
          
          $category_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_CATEGORIES."
                                          WHERE categories_id = '".(int)$categoryId."'");
          if (xtc_db_num_rows($category_query) < 1) {
              throw new Exception(sprintf('Category not found: %s', $categoryId));
          } else {
              $category = xtc_db_fetch_array($category_query);
          }
          
          $result = $this->encode_request($category);
          return $result;
      }

      /**
       * Read a category description by the given category id.
       *
       * @param int $categoryId The category id
       *
       * @throws Exception
       *
       * @return array The category data
       */
      public function GetCategoryDescription(int $categoryId): array
      {
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }
          
          $category_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                          WHERE categories_id = '".(int)$categoryId."'");
          if (xtc_db_num_rows($category_query) < 1) {
              throw new Exception(sprintf('Category description not found: %s', $categoryId));
          } else {
              $description = array();
              $categories_description_query = xtc_db_query("SELECT cd.*,
                                                                   l.code
                                                              FROM ".TABLE_CATEGORIES_DESCRIPTION." cd
                                                              JOIN ".TABLE_LANGUAGES." l
                                                                   ON l.languages_id = cd.language_id
                                                             WHERE cd.categories_id = '".(int)$categoryId."'");
              while ($categories_description = xtc_db_fetch_array($categories_description_query)) {
                  $code = $categories_description['code'];
                  unset($categories_description['code']);
              
                  $description[$code] = $categories_description;
              }
          }
          
          $result = $this->encode_request($description);
          return $result;
      }

      /**
       * Read categorys by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The category data
       */
      public function GetAllCategories($options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
          
          $conditions = [];
          if (preg_replace('/[^\d\,]/', '', $this->options['status']) != '') {
              $conditions[] = " categories_status IN (".preg_replace('/[^\d\,]/', '', $this->options['status']).") ";
          }
          if (preg_replace('/[^\d\,]/', '', $this->options['parent']) != '') {
              $conditions[] = " parent_id IN (".preg_replace('/[^\d\,]/', '', $this->options['parent']).") ";
          }
          if ((int)$this->options['from'] > 0) {
              $conditions[] = " date_added >= '".date('Y-m-d H:i:s', (int)$this->options['from'])."' ";
          }
          if ((int)$this->options['to'] > 0) {
              $conditions[] = " date_added <= '".date('Y-m-d H:i:s', (int)$this->options['to'])."' ";
          }
          
          if (count($conditions) > 0) {
            $where = " WHERE ".implode(' AND ', $conditions);
          }
                                              
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_CATEGORIES."
                                              ".$where);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Category found');
          }
          
          $data = [];
          $categories_query = xtc_db_query("SELECT categories_id
                                              FROM ".TABLE_CATEGORIES."
                                                   ".$where."
                                          ORDER BY categories_id ASC
                                             LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($categories = xtc_db_fetch_array($categories_query)) {
              $data[] = $this->GetSingleCategory($categories['categories_id']);
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
       * Insert a category by the given options.
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The category data
       */
      public function InsertCategory(array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if (isset($this->options[TABLE_CATEGORIES])) {
              $categories = $this->InsertUpdateCategory(0, $this->options[TABLE_CATEGORIES]);
              $categoryId = $categories['categories_id'];
          }
          
          if (!isset($categoryId)) {
              throw new Exception('Category ID required');
          } else {
              if (isset($this->options[TABLE_CATEGORIES_DESCRIPTION])) {
                  $categories_description = $this->InsertUpdateDescription($categoryId, $this->options[TABLE_CATEGORIES_DESCRIPTION]);
              }
          }
          
          return $this->GetSingleCategory($categoryId);
      }

      /**
       * Insert a category by the given category id and options.
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The category data
       */
      public function UpdateCategory(int $categoryId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          $category_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_CATEGORIES."
                                          WHERE categories_id = '".(int)$categoryId."'");
          if (xtc_db_num_rows($category_query) < 1) {
              throw new Exception(sprintf('Category not found: %s', $categoryId));
          } else {
              if (isset($this->options[TABLE_CATEGORIES])) {
                  $categories = $this->InsertUpdateCategory($categoryId, $this->options[TABLE_CATEGORIES]);
              }
          }
          
          $category_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                          WHERE categories_id = '".(int)$categoryId."'");
          if (xtc_db_num_rows($category_query) < 1) {
              throw new Exception(sprintf('Category description not found: %s', $categoryId));
          } else {
              if (isset($this->options[TABLE_CATEGORIES_DESCRIPTION])) {
                  $categories_description = $this->InsertUpdateDescription($categoryId, $this->options[TABLE_CATEGORIES_DESCRIPTION]);
              }
          }
          
          return $this->GetSingleCategory($categoryId);
      }

      /**
       * Insert or Update a category by the given category id.
       *
       * @param int $categoryId The category id
       * @param mixed[] $options
       *
       * @return array The category data
       */
      public function InsertUpdateCategory(int $categoryId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($categoryId > 0) {
              $action = 'update';
              $categories_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_CATEGORIES."
                                                 WHERE categories_id = '".(int)$categoryId."'");
              if (xtc_db_num_rows($categories_query) < 1) {
                  throw new Exception(sprintf('Category not found: %s', $categoryId));
              } else {
                  $categories = xtc_db_fetch_array($categories_query);
                  $categories['last_modified'] = 'now()';
              }
          } else {
              $action = 'insert';
              $categories = $this->getDefaultTableValues(TABLE_CATEGORIES);
              $categories['date_added'] = 'now()';
          }
          
          foreach ($categories as $key => $value) {
              if (isset($this->options[$key])) {
                  $categories[$key] = $this->options[$key];
              }
          }
          
          // Input validation
          $this->checkTableData(TABLE_CATEGORIES, $categories);
          unset($categories['categories_id']);
                    
          xtc_db_perform(TABLE_CATEGORIES, $categories, $action, "categories_id = '".(int)$categoryId."'");
          if ($action == 'insert') {
              $categoryId = xtc_db_insert_id();
          }          

          return $this->getCategory($categoryId);
      }

      /**
       * Insert or Update a category by the given category id.
       *
       * @param int $categoryId The category id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The category data
       */
      public function InsertUpdateDescription(int $categoryId, array $options): array
      {
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $categories_query = xtc_db_query("SELECT *
                                              FROM ".TABLE_CATEGORIES."
                                             WHERE categories_id = '".(int)$categoryId."'");
          if (xtc_db_num_rows($categories_query) < 1) {
              throw new Exception(sprintf('Category not found: %s', $categoryId));
          } else {
              $languages_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_LANGUAGES);
              while ($languages = xtc_db_fetch_array($languages_query)) { 
                  $categories_description_query = xtc_db_query("SELECT *
                                                                  FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                                                 WHERE categories_id = '".(int)$categoryId."'
                                                                   AND language_id = '".(int)$languages['languages_id']."'");
                  if (xtc_db_num_rows($categories_description_query) > 0) {
                      $categories_description = xtc_db_fetch_array($categories_description_query);

                      foreach ($categories_description as $key => $value) {
                          if (isset($this->options[$languages['code']][$key])) {
                              $categories_description[$key] = $this->options[$languages['code']][$key];
                          }
                      }

                      // Input validation
                      $this->checkTableData(TABLE_CATEGORIES_DESCRIPTION, $categories_description);
                      xtc_db_perform(TABLE_CATEGORIES_DESCRIPTION, $categories_description, 'update', "categories_id = '".(int)$categoryId."' AND language_id = '".(int)$languages['languages_id']."'");
                  } elseif (isset($this->options[$languages['code']])) {
                      $categories_description = $this->getDefaultTableValues(TABLE_CATEGORIES_DESCRIPTION);
                      $categories_description['categories_id'] = (int)$categoryId;
                      $categories_description['language_id'] = (int)$languages['languages_id'];
                
                      foreach ($categories_description as $key => $value) {
                          if (isset($this->options[$languages['code']][$key])) {
                              $categories_description[$key] = $this->options[$languages['code']][$key];
                          }
                      }

                      // Input validation
                      $this->checkTableData(TABLE_CATEGORIES_DESCRIPTION, $categories_description);
                      xtc_db_perform(TABLE_CATEGORIES_DESCRIPTION, $categories_description);
                  }
              }            
          }

          return $this->getCategoryDescription($categoryId);
      }

      /**
       * Insert or update images of a category by the given category id.
       *
       * @param int $categoryId The category id
       *
       * @throws Exception
       *
       * @return void
       */
      public function InsertUpdateImages(int $categoryId): void
      {
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }
          
          $category_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CATEGORIES."
                                           WHERE categories_id = '".(int)$categoryId."'");
          if (xtc_db_num_rows($category_query) < 1) {
              throw new Exception(sprintf('Category not found: %s', $categoryId));
          } else {
              define('_VALID_XTC', true);
          
              // include needed classes
              require_once (DIR_FS_CATALOG.DIR_ADMIN.'includes/classes/'.IMAGE_MANIPULATOR);
          
              foreach ($this->images_type_array as $image_type) {
                  if ($categories_image = xtc_try_upload('categories_image'.$image_type, DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/original_images/', '777', $this->accepted_image_files_extensions, $this->accepted_image_files_mime_types)) {
                      $categories_image_name = preg_replace('/[^\d\w\-\_\.]/', '', $categories_image->filename);
                  
                      rename(DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/original_images/'.$categories_image->filename, DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/original_images/'.$categories_image_name);
                  
                      //image chmod
                      chmod(DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/original_images/'.$categories_image_name, 0644);

                      xtc_db_query("UPDATE ".TABLE_CATEGORIES."
                                       SET categories_image".$image_type." = '".xtc_db_input($categories_image_name)."'
                                     WHERE categories_id = '".(int)$categoryId."'");
                     
                      if (is_file(DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/'.$categories_image_name)) {
                         unlink(DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/'.$categories_image_name);
                      }

                      $a = new \image_manipulation(DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/original_images/'.$categories_image_name, constant('CATEGORIES_IMAGE'.strtoupper($image_type).'_WIDTH'), constant('CATEGORIES_IMAGE'.strtoupper($image_type).'_HEIGHT'), DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/'.$categories_image_name, IMAGE_QUALITY, '');
                      $a->create();
                  }
              }
          }
      }

      /**
       * Delete a image by the given category id.
       *
       * @param int $categoryId The category id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteImages(int $categoryId): void
      {
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }

          $category_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CATEGORIES."
                                           WHERE categories_id = '".(int)$categoryId."'");
          if (xtc_db_num_rows($category_query) < 1) {
              throw new Exception(sprintf('Category not found: %s', $categoryId));
          } else {
              $category_image_query = xtc_db_query("SELECT *
                                                      FROM ".TABLE_CATEGORIES." 
                                                     WHERE categories_id = '".(int)$categoryId."'");
              $category_image = xtc_db_fetch_array($category_image_query);

              foreach ($this->images_type_array as $image_type) {
                  $duplicate_image_query = xtc_db_query("SELECT count(*) AS total 
                                                           FROM ".TABLE_CATEGORIES." 
                                                          WHERE categories_image".$image_type." = '".xtc_db_input($category_image['categories_image'.$image_type])."'");
                  $duplicate_image = xtc_db_fetch_array($duplicate_image_query);

                  if ($duplicate_image['total'] < 2) {
                      xtc_db_query("UPDATE ".TABLE_CATEGORIES."
                                       SET categories_image".$image_type." = ''
                                     WHERE categories_id = '".(int)$categoryId."'");

                      if (is_file(DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/'.$category_image['categories_image'.$image_type])) {
                          unlink(DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/'.$category_image['categories_image'.$image_type]);
                      }
                      if (is_file(DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/original_images/'.$category_image['categories_image'.$image_type])) {
                          unlink(DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/original_images/'.$category_image['categories_image'.$image_type]);
                      }        
                  }
              }
          }
      }
      
      /**
       * Delete a category by the given category id.
       *
       * @param int $categoryId The category id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteCategory(int $categoryId): void
      {
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }

          $category_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CATEGORIES."
                                           WHERE categories_id = '".(int)$categoryId."'");
          if (xtc_db_num_rows($category_query) < 1) {
              throw new Exception(sprintf('Category not found: %s', $categoryId));
          } else {
              $subcategories_query = xtc_db_query("SELECT *
                                                     FROM ".TABLE_CATEGORIES."
                                                    WHERE parent_id = '".(int)$categoryId."'");
              $count = xtc_db_num_rows($subcategories_query);
              if ($count > 0) {
                  throw new Exception(sprintf('Category can not get deleted due to connected categories: %s', $count));
              } else {
                  $products_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_PRODUCTS_TO_CATEGORIES." 
                                                   WHERE categories_id = '".(int)$categoryId."'");
                  $count = xtc_db_num_rows($products_query);
                  if ($count > 0) {
                      throw new Exception(sprintf('Category can not get deleted due to connected products: %s', $count));
                  } else {
                      $this->DeleteImages($categoryId);

                      xtc_db_query("DELETE FROM ".TABLE_CATEGORIES." WHERE categories_id = '".(int)$categoryId."'");
                      xtc_db_query("DELETE FROM ".TABLE_CATEGORIES_DESCRIPTION." WHERE categories_id = '".(int)$categoryId."'");
                      xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE categories_id = '".(int)$categoryId."'");

                      $this->logger->info(sprintf('Category deleted successfully: %s', $categoryId));
                  }
              }
          }          
      }
      
  }
