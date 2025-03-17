<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Product;

  use api\v1\Action\BaseAction;
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
       * @var mixed[]
       */
      protected $images_type_array = [
        'popup',
        'info',
        'thumbnail',
        'mini',
        'midi',
      ];

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
       * @throws Exception
       *
       * @return array The product data
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
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertUpdateCategories(int $productId, array $options): array
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

                  foreach ($categories as $key => $value) {
                      if (isset($this->options[$key])) {
                          $categories[$key] = $this->options[$key];
                      }
                  }

                  // Input validation
                  $this->checkTableData(TABLE_PRODUCTS_TO_CATEGORIES, $categories);
                  xtc_db_perform(TABLE_PRODUCTS_TO_CATEGORIES, $categories, $action, "products_id = '".(int)$productId."' AND categories_id = '".(int)$this->options['categories_id']."'");
              }
          }

          return $this->GetProductCategories($productId);
      }

      /**
       * Insert or update image of a product by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertUpdateImage(int $productId): array
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

              if ($products_image = xtc_try_upload('products_image', DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/original_images/', '777', $this->accepted_image_extensions, $this->accepted_image_mime_types)) {
                  $products_image_name = preg_replace('/[^\d\w\-\_\.]/', '', $products_image->filename);

                  rename(DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/original_images/'.$products_image->filename, DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/original_images/'.$products_image_name);

                  //image chmod
                  chmod(DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/original_images/'.$products_image_name, 0644);

                  xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                                   SET products_image = '".xtc_db_input($products_image_name)."'
                                 WHERE products_id = '".(int)$productId."'");

                  foreach ($this->images_type_array as $image_type) {
                      $a = new \image_manipulation(DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/original_images/'.$products_image_name, constant('PRODUCT_IMAGE_'.strtoupper($image_type).'_WIDTH'), constant('PRODUCT_IMAGE_'.strtoupper($image_type).'_HEIGHT'), DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/'.strtolower($image_type).'_images/'.$products_image_name, IMAGE_QUALITY, '');
                      $a->create();
                  }
              }
          }

          return $this->getProduct($productId);
      }

      /**
       * Insert or update more images of a product by the given product id and image nr.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertUpdateImages(int $productId, array $options): array
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
              define('_VALID_XTC', true);

              // include needed classes
              require_once (DIR_FS_CATALOG.DIR_ADMIN.'includes/classes/'.IMAGE_MANIPULATOR);

              $where = '';
              if (isset($this->options['image_id'])) {
                  $imageId = (int)$this->options['image_id'];
                  $where = "products_id = '".(int)$productId."' AND image_id = '".(int)$imageId."'";
                  $images_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_PRODUCTS_IMAGES."
                                                 WHERE ".$where);
                  if (xtc_db_num_rows($images_query) > 0) {
                      $action = 'update';
                      $images = xtc_db_fetch_array($images_query);
                  } else {
                      $action = 'insert';
                      $images = $this->getDefaultTableValues(TABLE_PRODUCTS_IMAGES);
                      $images['products_id'] = (int)$productId;
                  }
              } else {
                  $action = 'insert';
                  $images = $this->getDefaultTableValues(TABLE_PRODUCTS_IMAGES);
                  $images['products_id'] = (int)$productId;
              }

              foreach ($images as $key => $value) {
                  if (isset($this->options[$key])) {
                      $images[$key] = $this->options[$key];
                  }
              }

              // Input validation
              $this->checkTableData(TABLE_PRODUCTS_IMAGES, $images);
              xtc_db_perform(TABLE_PRODUCTS_IMAGES, $images, $action, $where);
              
              if (!isset($imageId)) {
                $imageId = xtc_db_insert_id();
              }
              $this->InsertUpdateImagesDescription($productId, $imageId, $options);
              
              if ($products_image = xtc_try_upload('image_name', DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/original_images/', '777', $this->accepted_image_extensions, $this->accepted_image_mime_types)) {
                  $products_image_name = preg_replace('/[^\d\w\-\_\.]/', '', $products_image->filename);
                  
                  rename(DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/original_images/'.$products_image->filename, DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/original_images/'.$products_image_name);

                  //image chmod
                  chmod(DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/original_images/'.$products_image_name, 0644);

                  xtc_db_query("UPDATE ".TABLE_PRODUCTS_IMAGES."
                                   SET image_name = '".xtc_db_input($products_image_name)."'
                                 WHERE image_id = '".(int)$imageId."'");

                  foreach ($this->images_type_array as $image_type) {
                      $a = new \image_manipulation(DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/original_images/'.$products_image_name, constant('PRODUCT_IMAGE_'.strtoupper($image_type).'_WIDTH'), constant('PRODUCT_IMAGE_'.strtoupper($image_type).'_HEIGHT'), DIR_FS_CATALOG.DIR_WS_IMAGES.'product_images/'.strtolower($image_type).'_images/'.$products_image_name, IMAGE_QUALITY, '');
                      $a->create();
                  }
              }
          }

          return $this->GetProductImages($productId);
      }

      /**
       * Insert or update more images of a product by the given product id and image nr.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertUpdateImagesDescription(int $productId, int $imageId, array $options): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }
          if (empty($imageId)) {
              throw new Exception('Image ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_IMAGES."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->Exception === true) {
              throw new Exception(sprintf('Product images not found: %s', $productId));
          } else {
              $languages_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_LANGUAGES);
              while ($languages = xtc_db_fetch_array($languages_query)) {
                  $image_description_query = xtc_db_query("SELECT *
                                                             FROM ".TABLE_PRODUCTS_IMAGES_DESCRIPTION."
                                                            WHERE products_id = '".(int)$productId."'
                                                              AND image_id = '".(int)$imageId."'
                                                              AND language_id = '".(int)$languages['languages_id']."'");
                  if (xtc_db_num_rows($image_description_query) > 0) {
                      $action = 'update';
                      $image_description = xtc_db_fetch_array($image_description_query);
                  } else {
                      $action = 'insert';
                      $image_description = $this->getDefaultTableValues(TABLE_PRODUCTS_IMAGES_DESCRIPTION);
                      $image_description['products_id'] = (int)$productId;
                      $image_description['image_id'] = (int)$imageId;
                      $image_description['language_id'] = (int)$languages['languages_id'];
                  }

                  foreach ($image_description as $key => $value) {
                      if (isset($this->options[$languages['code']][$key])) {
                          $image_description[$key] = $this->options[$languages['code']][$key];
                      }
                  }

                  // Input validation
                  $this->checkTableData(TABLE_PRODUCTS_IMAGES_DESCRIPTION, $image_description);
                  xtc_db_perform(TABLE_PRODUCTS_IMAGES_DESCRIPTION, $image_description, $action, "products_id = '".(int)$productId."' AND image_id = '".(int)$imageId."' AND language_id = '".(int)$languages['languages_id']."'");
              }
          }

          return $this->GetProductImagesDescription($productId, $imageId);
      }

      /**
       * Insert or Update a product xsell by the given product id.
       *
       * @param int $productId The product id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertUpdateXsell(int $productId, array $options): array
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
              if (!isset($this->options['xsell_id'])) {
                  throw new Exception(sprintf('Xsell ID required'));
              } else {
                  $xsell_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_PRODUCTS_XSELL."
                                                WHERE products_id = '".(int)$productId."'
                                                  AND xsell_id = '".(int)$this->options['xsell_id']."'");
                  if (xtc_db_num_rows($xsell_query) > 0) {
                      $action = 'update';
                      $xsell = xtc_db_fetch_array($xsell_query);
                  } else {
                      $action = 'insert';
                      $xsell = $this->getDefaultTableValues(TABLE_PRODUCTS_XSELL);
                      $xsell['products_id'] = (int)$productId;
                  }

                  foreach ($xsell as $key => $value) {
                      if (isset($this->options[$key])) {
                          $xsell[$key] = $this->options[$key];
                      }
                  }

                  // Input validation
                  $this->checkTableData(TABLE_PRODUCTS_XSELL, $xsell);
                  xtc_db_perform(TABLE_PRODUCTS_XSELL, $xsell, $action, "products_id = '".(int)$productId."' AND xsell_id = '".(int)$this->options['xsell_id']."'");
              }
          }

          return $this->GetProductXsell($productId);
      }

      /**
       * Insert or Update a product attributes by the given product id.
       *
       * @param int $productId The product id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertUpdateAttributes(int $productId, array $options): array
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
              if (!isset($this->options['options_id']) || !isset($this->options['options_values_id'])) {
                  throw new Exception('Options ID and Options Values ID required');
              } else {
                  $options_query = xtc_db_query("SELECT *
                                                   FROM ".TABLE_PRODUCTS_OPTIONS."
                                                  WHERE products_options_id = '".(int)$this->options['options_id']."'");
                  if (xtc_db_num_rows($options_query) < 1) {
                      throw new Exception(sprintf('Options ID invalid'));
                  }
                  
                  $values_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_PRODUCTS_OPTIONS_VALUES."
                                                 WHERE products_options_values_id = '".(int)$this->options['options_values_id']."'");
                  if (xtc_db_num_rows($values_query) < 1) {
                      throw new Exception(sprintf('Options Values ID invalid'));
                  }

                  $options_values_query = xtc_db_query("SELECT *
                                                          FROM ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS."
                                                         WHERE products_options_id = '".(int)$this->options['options_id']."'
                                                           AND products_options_values_id = '".(int)$this->options['options_values_id']."'");
                  if (xtc_db_num_rows($options_values_query) < 1) {
                      throw new Exception(sprintf('Options ID and Options Values ID invalid'));
                  }
                  
                  $where = '';
                  if (isset($this->options['products_attributes_id'])) {
                      $where = "AND products_attributes_id = '".(int)$this->options['products_attributes_id']."'";
                      $attributes_query = xtc_db_query("SELECT *
                                                          FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                                                         WHERE products_id = '".(int)$productId."'
                                                               ".$where);
                      if (xtc_db_num_rows($attributes_query) < 1) {
                          throw new Exception(sprintf('Attributes ID invalid'));
                      } else {
                          $action = 'update';
                          $attributes = xtc_db_fetch_array($attributes_query);
                      }
                  } else {
                      $action = 'insert';
                      $attributes = $this->getDefaultTableValues(TABLE_PRODUCTS_ATTRIBUTES);
                      $attributes['products_id'] = (int)$productId;
                  }
    
                  foreach ($attributes as $key => $value) {
                      if (isset($this->options[$key])) {
                          $attributes[$key] = $this->options[$key];
                      }
                  }
    
                  // Input validation
                  $this->checkTableData(TABLE_PRODUCTS_ATTRIBUTES, $attributes);
                  xtc_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $attributes, $action, "products_id = '".(int)$productId."' ".$where);
              }
          }

          return $this->GetProductAttributes($productId);
      }

      /**
       * Insert or Update a product tags by the given product id.
       *
       * @param int $productId The product id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertUpdateTags(int $productId, array $options): array
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
              if (!isset($this->options['options_id']) || !isset($this->options['values_id'])) {
                  throw new Exception('Options ID and Values ID required');
              } else {
                  $options_query = xtc_db_query("SELECT *
                                                   FROM ".TABLE_PRODUCTS_TAGS_OPTIONS."
                                                  WHERE options_id = '".(int)$this->options['options_id']."'");
                  if (xtc_db_num_rows($options_query) < 1) {
                      throw new Exception(sprintf('Options ID invalid'));
                  }
                  
                  $values_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_PRODUCTS_TAGS_VALUES."
                                                 WHERE options_id = '".(int)$this->options['options_id']."'
                                                   AND values_id = '".(int)$this->options['values_id']."'");
                  if (xtc_db_num_rows($values_query) < 1) {
                      throw new Exception(sprintf('Values ID invalid'));
                  }
                  
                  $where = '';
                  if (isset($this->options['products_tags_id'])) {
                      $where = "AND products_tags_id = '".(int)$this->options['products_tags_id']."'";
                      $tags_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_PRODUCTS_TAGS."
                                                   WHERE products_id = '".(int)$productId."'
                                                         ".$where);
                      if (xtc_db_num_rows($tags_query) < 1) {
                          throw new Exception(sprintf('Tags ID invalid'));
                      } else {
                          $action = 'update';
                          $tags = xtc_db_fetch_array($tags_query);
                      }
                  } else {
                      $action = 'insert';
                      $tags = $this->getDefaultTableValues(TABLE_PRODUCTS_TAGS);
                      $tags['products_id'] = (int)$productId;
                  }
    
                  foreach ($tags as $key => $value) {
                      if (isset($this->options[$key])) {
                          $tags[$key] = $this->options[$key];
                      }
                  }
    
                  // Input validation
                  $this->checkTableData(TABLE_PRODUCTS_TAGS, $tags);
                  xtc_db_perform(TABLE_PRODUCTS_TAGS, $tags, $action, "products_id = '".(int)$productId."' ".$where);
              }
          }

          return $this->GetProductTags($productId);
      }

      /**
       * Insert or Update a product content by the given product id.
       *
       * @param int $productId The product id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertUpdateContent(int $productId, array $options): array
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
                  $where = '';
                  if (isset($this->options[$languages['code']]['content_id'])) {
                      $contentId = (int)$this->options[$languages['code']]['content_id'];
                      $where = "AND content_id = '".(int)$contentId."'";
                      $content_query = xtc_db_query("SELECT *
                                                       FROM ".TABLE_PRODUCTS_CONTENT."
                                                      WHERE products_id = '".(int)$productId."'
                                                            ".$where);
                      if (xtc_db_num_rows($content_query) < 1) {
                          throw new Exception(sprintf('Content ID invalid'));
                      } else {
                          $action = 'update';
                          $content = xtc_db_fetch_array($content_query);
                      }
                  } else {
                      $action = 'insert';
                      $content = $this->getDefaultTableValues(TABLE_PRODUCTS_CONTENT);
                      $content['products_id'] = (int)$productId;
                      $content['languages_id'] = (int)$languages['languages_id'];
                  }

                  foreach ($content as $key => $value) {
                      if (isset($this->options[$languages['code']][$key])) {
                          $content[$key] = $this->options[$languages['code']][$key];
                      }
                  }

                  // Input validation
                  $this->checkTableData(TABLE_PRODUCTS_CONTENT, $content);
                  xtc_db_perform(TABLE_PRODUCTS_CONTENT, $content, $action, "products_id = '".(int)$productId."' ".$where);
                  
                  if (!isset($contentId)) {
                      $contentId = xtc_db_insert_id();
                  }
                  
                  if ($content_file = xtc_try_upload(array($languages['code'] => 'content_file'), DIR_FS_CATALOG.'media/products/', '777', array_merge($this->accepted_image_extensions, $this->accepted_file_extensions, $this->accepted_extfile_extensions, $this->accepted_audio_extensions, $this->accepted_movie_extensions, $this->accepted_compressed_extensions), array_merge($this->accepted_image_mime_types, $this->accepted_file_mime_types, $this->accepted_extfile_mime_types, $this->accepted_audio_mime_types, $this->accepted_movie_mime_types, $this->accepted_compressed_mime_types))) {
                      $content_file_name = preg_replace('/[^\d\w\-\_\.]/', '', $content_file->filename);
                      
                      rename(DIR_FS_CATALOG.'media/products/'.$content_file->filename, DIR_FS_CATALOG.'media/products/'.$content_file_name);
                      copy(DIR_FS_CATALOG.'media/products/'.$content_file_name, DIR_FS_CATALOG.'media/products/backup/'.$content_file_name);
    
                      //content chmod
                      chmod(DIR_FS_CATALOG.'media/products/'.$content_file_name, 0644);

                      xtc_db_query("UPDATE ".TABLE_PRODUCTS_CONTENT."
                                       SET content_file = '".xtc_db_input($content_file_name)."'
                                     WHERE content_id = '".(int)$contentId."'");
                  }
              }          
          }

          return $this->GetProductContent($productId);
      }

      /**
       * Insert or Update a product specials by the given product id.
       *
       * @param int $productId The product id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertUpdateSpecials(int $productId, array $options): array
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
              $where = '';
              if (isset($this->options['specials_id'])) {
                  $where = "AND specials_id = '".(int)$this->options['specials_id']."'";
                  $specials_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_SPECIALS."
                                                   WHERE products_id = '".(int)$productId."'
                                                         ".$where);
                  if (xtc_db_num_rows($specials_query) < 1) {
                      throw new Exception(sprintf('Specials ID invalid'));
                  } else {
                      $action = 'update';
                      $specials = xtc_db_fetch_array($specials_query);
                      $specials['specials_last_modified'] = 'now()';
                  }
              } else {
                  $action = 'insert';
                  $specials = $this->getDefaultTableValues(TABLE_SPECIALS);
                  $specials['products_id'] = (int)$productId;
                  $specials['specials_date_added'] = 'now()';
              }

              foreach ($specials as $key => $value) {
                  if (isset($this->options[$key])) {
                      $specials[$key] = $this->options[$key];
                  }
              }

              // Input validation
              $this->checkTableData(TABLE_SPECIALS, $specials);
              xtc_db_perform(TABLE_SPECIALS, $specials, $action, "products_id = '".(int)$productId."' ".$where);
          }

          return $this->GetProductSpecials($productId);
      }

      /**
       * Insert or Update a product offer by the given product id.
       *
       * @param int $productId The product id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function InsertUpdatePersonalOffer(int $productId, array $options): array
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
              if (!isset($this->options['status_id'])) {
                  throw new Exception('Status ID required');
              } else {
                  $where = '';
                  if (isset($this->options['price_id'])) {
                      $where = "AND price_id = '".(int)$this->options['price_id']."'";
                      $personal_offer_query = xtc_db_query("SELECT *
                                                              FROM ".TABLE_PERSONAL_OFFERS_BY.$this->options['status_id']."
                                                             WHERE products_id = '".(int)$productId."'
                                                                   ".$where);
                      if (xtc_db_num_rows($personal_offer_query) < 1) {
                          throw new Exception(sprintf('Price ID invalid'));
                      } else {
                          $action = 'update';
                          $personal_offer = xtc_db_fetch_array($personal_offer_query);
                      }
                  } else {
                      $action = 'insert';
                      $personal_offer = $this->getDefaultTableValues(TABLE_PERSONAL_OFFERS_BY.$this->options['status_id']);
                      $personal_offer['products_id'] = (int)$productId;
                  }
    
                  foreach ($personal_offer as $key => $value) {
                      if (isset($this->options[$key])) {
                          $personal_offer[$key] = $this->options[$key];
                      }
                  }
    
                  // Input validation
                  $this->checkTableData(TABLE_PERSONAL_OFFERS_BY.$this->options['status_id'], $personal_offer);
                  xtc_db_perform(TABLE_PERSONAL_OFFERS_BY.$this->options['status_id'], $personal_offer, $action, "products_id = '".(int)$productId."' ".$where);
              }
          }

          return $this->GetProductPersonalOffer($productId);
      }

  }
