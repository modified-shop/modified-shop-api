<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Category;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  final class CategoryAction extends BaseAction
  {
      use CategoryGetAction;
      use CategoryDeleteAction;

      /**
       * @var mixed[]
       */
      protected $images_type_array = [
        '',
        '_list',
        '_mobile'
      ];

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
       * Insert or Update a category product by the given category id.
       *
       * @param int $categoryId The category id
       * @param mixed[] $options
       *
       * @return array The category data
       */
      public function InsertUpdateProducts(int $categoryId, array $options): array
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
              if (!isset($this->options['products_id'])) {
                  throw new Exception(sprintf('Product ID required'));
              } else {
                  $products_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                                   WHERE categories_id = '".(int)$categoryId."'
                                                     AND products_id = '".(int)$this->options['products_id']."'");
                  if (xtc_db_num_rows($products_query) > 0) {
                      $action = 'update';
                      $products = xtc_db_fetch_array($products_query);
                  } else {
                      $action = 'insert';
                      $products = $this->getDefaultTableValues(TABLE_PRODUCTS_TO_CATEGORIES);
                      $products['categories_id'] = (int)$categoryId;
                  }

                  foreach ($products as $key => $value) {
                      if (isset($this->options[$key])) {
                          $products[$key] = $this->options[$key];
                      }
                  }

                  // Input validation
                  $this->checkTableData(TABLE_PRODUCTS_TO_CATEGORIES, $products);
                  xtc_db_perform(TABLE_PRODUCTS_TO_CATEGORIES, $products, $action, "categories_id = '".(int)$categoryId."' AND products_id = '".(int)$this->options['products_id']."'");
              }
          }

          return $this->GetCategoryProducts($categoryId);
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
      
  }
