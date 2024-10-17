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

  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;

  // include needed functions
  //require_once(DIR_FS_INC.'get_products_status_by_id.inc.php');
  require_once(DIR_FS_INC.'xtc_date_long.inc.php');
  require_once(DIR_FS_INC.'xtc_get_customers_statuses.inc.php');
  
  /**
   * Service.
   */
  final class ProductAction extends BaseAction
  {
      use ProductGetAction;
      use ProductDeleteAction;

      /**
       * Insert a product by the given options.
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertProduct(array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if (isset($this->options[TABLE_PRODUCTS])) {
              $products = $this->InsertUpdateProduct(0, $this->options[TABLE_PRODUCTS]);
              $productId = $products['products_id'];
          }

          if (!isset($productId)) {
              throw new Exception('Product ID required');
          } else {
              if (isset($this->options[TABLE_PRODUCTS_DESCRIPTION])) {
                  $products_description = $this->InsertUpdateDescription($productId, $this->options[TABLE_PRODUCTS_DESCRIPTION]);
              }
              if (isset($this->options[TABLE_PRODUCTS_TO_CATEGORIES])) {
                  $products_description = $this->InsertUpdateCategories($productId, $this->options[TABLE_PRODUCTS_TO_CATEGORIES]);
              }
          }

          return $this->getProductDetails($productId);
      }

      /**
       * Insert or Update a product by the given product id.
       *
       * @param int $productId The product id
       * @param mixed[] $options
       *
       * @return array The product data
       * @throws Exception
       */
      public function InsertUpdateProduct(int $productId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if ($productId > 0) {
              $action = 'update';
              $products_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_PRODUCTS."
                                               WHERE products_id = '".(int)$productId."'");
              if (xtc_db_num_rows($products_query) < 1) {
                  throw new Exception(sprintf('Product not found: %s', $productId));
              } else {
                  $products = xtc_db_fetch_array($products_query);
                  $products['products_last_modified'] = 'now()';
              }
          } else {
              $action = 'insert';
              $products = $this->getDefaultTableValues(TABLE_PRODUCTS);
              $products['products_date_added'] = 'now()';
          }

          foreach ($products as $key => $value) {
              if (isset($this->options[$key])) {
                  $products[$key] = $this->options[$key];
              }
          }

          // Input validation
          $this->checkTableData(TABLE_PRODUCTS, $products);
          unset($products['products_id']);

          xtc_db_perform(TABLE_PRODUCTS, $products, $action, "products_id = '".(int)$productId."'");
          if ($action == 'insert') {
              $productId = xtc_db_insert_id();
          }

          return $this->getProduct($productId);
      }

      /**
       * Insert or Update a product by the given product id.
       *
       * @param int $productId The product id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertUpdateDescription(int $productId, array $options): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $products_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_PRODUCTS."
                                           WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($products_query) < 1) {
              throw new Exception(sprintf('Product not found: %s', $productId));
          } else {
              $languages_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_LANGUAGES);
              while ($languages = xtc_db_fetch_array($languages_query)) {
                  $products_description_query = xtc_db_query("SELECT *
                                                                FROM ".TABLE_PRODUCTS_DESCRIPTION."
                                                               WHERE products_id = '".(int)$productId."'
                                                                 AND language_id = '".(int)$languages['languages_id']."'");
                  if (xtc_db_num_rows($products_description_query) > 0) {
                      $action = 'update';
                      $products_description = xtc_db_fetch_array($products_description_query);
                  } else {
                      $action = 'insert';
                      $products_description = $this->getDefaultTableValues(TABLE_PRODUCTS_DESCRIPTION);
                      $products_description['products_id'] = (int)$productId;
                      $products_description['language_id'] = (int)$languages['languages_id'];
                  }

                  foreach ($products_description as $key => $value) {
                      if (isset($this->options[$languages['code']][$key])) {
                          $products_description[$key] = $this->options[$languages['code']][$key];
                      }
                  }

                  // Input validation
                  $this->checkTableData(TABLE_PRODUCTS_DESCRIPTION, $products_description);
                  xtc_db_perform(TABLE_PRODUCTS_DESCRIPTION, $products_description, $action, "products_id = '".(int)$productId."' AND language_id = '".(int)$languages['languages_id']."'");
              }
          }

          return $this->getProductDescription($productId);
      }

      /**
       * Insert or Update a category by the given product id.
       *
       * @param int $productId The product id
       * @param mixed[] $options
       *
       * @return array The product data
       * @throws Exception
       */
      public function InsertUpdateCategories(int $productId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $products_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_PRODUCTS."
                                           WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($products_query) < 1) {
              throw new Exception(sprintf('Product not found: %s', $productId));
          } else {
              if (!isset($this->options['categories_id'])) {
                  throw new Exception(sprintf('Category ID required'));
              } else {
                  $categories_query = xtc_db_query("SELECT *
                                                      FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                                     WHERE products_id = '".(int)$productId."'
                                                       AND categories_id = '".(int)$this->options['categories_id']."'");
                  if (xtc_db_num_rows($categories_query) > 0) {
                      $action = 'update';
                      $categories = xtc_db_fetch_array($categories_query);
                  } else {
                      $action = 'insert';
                      $categories = $this->getDefaultTableValues(TABLE_PRODUCTS_TO_CATEGORIES);
                      $categories['products_id'] = (int)$productId;
                  }
              }
          }

          foreach ($categories as $key => $value) {
              if (isset($this->options[$key])) {
                  $categories[$key] = $this->options[$key];
              }
          }

          // Input validation
          $this->checkTableData(TABLE_PRODUCTS_TO_CATEGORIES, $categories);
          xtc_db_perform(TABLE_PRODUCTS_TO_CATEGORIES, $categories, $action, "products_id = '".(int)$productId."' AND categories_id = '".(int)$this->options['categories_id']."'");

          return $this->GetProductCategories($productId);
      }

      /**
       * Insert or update images of a product by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function InsertUpdateImages(int $productId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $product_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_PRODUCTS."
                                           WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1) {
              throw new Exception(sprintf('Product not found: %s', $productId));
          } else {
              define('_VALID_XTC', true);

              // include needed classes
              require_once (DIR_FS_CATALOG.DIR_ADMIN.'includes/classes/'.IMAGE_MANIPULATOR);

              foreach ($this->images_type_array as $image_type) {
                  if ($products_image = xtc_try_upload('products_image'.$image_type, DIR_FS_CATALOG.DIR_WS_IMAGES.'products/original_images/', '777', $this->accepted_image_files_extensions, $this->accepted_image_files_mime_types)) {
                      $products_image_name = preg_replace('/[^\d\w\-\_\.]/', '', $products_image->filename);

                      rename(DIR_FS_CATALOG.DIR_WS_IMAGES.'products/original_images/'.$products_image->filename, DIR_FS_CATALOG.DIR_WS_IMAGES.'products/original_images/'.$products_image_name);

                      //image chmod
                      chmod(DIR_FS_CATALOG.DIR_WS_IMAGES.'products/original_images/'.$products_image_name, 0644);

                      xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                                       SET products_image".$image_type." = '".xtc_db_input($products_image_name)."'
                                     WHERE products_id = '".(int)$productId."'");

                      if (is_file(DIR_FS_CATALOG.DIR_WS_IMAGES.'products/'.$products_image_name)) {
                          unlink(DIR_FS_CATALOG.DIR_WS_IMAGES.'products/'.$products_image_name);
                      }

                      $a = new \image_manipulation(DIR_FS_CATALOG.DIR_WS_IMAGES.'products/original_images/'.$products_image_name, constant('CATEGORIES_IMAGE'.strtoupper($image_type).'_WIDTH'), constant('CATEGORIES_IMAGE'.strtoupper($image_type).'_HEIGHT'), DIR_FS_CATALOG.DIR_WS_IMAGES.'products/'.$products_image_name, IMAGE_QUALITY, '');
                      $a->create();
                  }
              }
          }
      }

  }
