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
      /**
       * @var mixed[]
       */
      protected $options = [
          "status" => null,
          "from" => null,
          "to" => null,
          "page" => 1,
          "limit" => 10,
      ];

      /**
       * @var LoggerInterface
       */
      protected $logger;

      /**
       * The constructor.
       *
       * @param LoggerHandler $LoggerHandler The logger factory
       */
      public function __construct(
          LoggerHandler $LoggerHandler
      ) {
          $this->logger = $LoggerHandler->createLogger();
      }

      /**
       * Read an product by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function getProductDetails(int $productId): array
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
              $result = [
                  'products' => $this->GetProduct($productId, false),
                  'products_description' => $this->GetProductDescription($productId, false),
                  'products_to_categories' => $this->GetProductCategories($productId, false),
                  'products_images' => $this->GetProductImages($productId, false),
                  'products_xsell' => $this->GetProductXsell($productId, false),
                  'products_attributes' => $this->GetProductAttributes($productId, false),
                  'products_tags' => $this->GetProductTags($productId, false),
                  'specials' => $this->GetProductSpecials($productId, false),
              ];

              $result = $this->encode_request($result);
              return $result;
          }
      }

      /**
       * Read products by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function getProducts($options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
          
          $conditions = [];
          if (!empty(preg_replace('/[^\d\,]/', '', $this->options['status']))) {
              $conditions[] = " products_status IN (".preg_replace('/[^\d\,]/', '', $this->options['status']).") ";
          }
          if ((int)$this->options['from'] > 0) {
              $conditions[] = " products_date_added >= '".date('Y-m-d H:i:s', (int)$this->options['from'])."' ";
          }
          if ((int)$this->options['to'] > 0) {
              $conditions[] = " products_date_added <= '".date('Y-m-d H:i:s', (int)$this->options['to'])."' ";
          }
          
          if (count($conditions) > 0) {
              $where = " WHERE ".implode(' AND ', $conditions);
          }
          
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_PRODUCTS."
                                              ".$where);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Product found');
          }
          
          $data = [];
          $products_query = xtc_db_query("SELECT products_id
                                             FROM ".TABLE_PRODUCTS."
                                                  ".$where."
                                         ORDER BY products_date_added DESC
                                            LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($products = xtc_db_fetch_array($products_query)) {
              $data[] = $this->getProductDetails($products['products_id']);
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
       * Delete an product by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function deleteProduct(int $productId): void
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
            $product_content_query = xtc_db_query("SELECT content_file 
                                                     FROM ".TABLE_PRODUCTS_CONTENT." 
                                                    WHERE products_id = '".(int)$productId."'");
            while ($product_content = xtc_db_fetch_array($product_content_query)) {
               $duplicate_content_query = xtc_db_query("SELECT count(*) AS total 
                                                          FROM ".TABLE_PRODUCTS_CONTENT." 
                                                         WHERE content_file = '".xtc_db_input($product_content['content_file'])."' 
                                                           AND products_id != '".(int)$productId."'");
               $duplicate_content = xtc_db_fetch_array($duplicate_content_query);
               if ($duplicate_content['total'] == 0) {
                 if (is_file(DIR_FS_CATALOG.'media/products/'.$product_content['content_file'])) {
                   unlink(DIR_FS_CATALOG.'media/products/'.$product_content['content_file']);
                 }
               }
               xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_CONTENT." WHERE products_id = '".(int)$productId."' AND (content_file = '".xtc_db_input($product_content['content_file'])."' OR content_file = '')");
            }

            $product_image_query = xtc_db_query("SELECT products_image 
                                                   FROM ".TABLE_PRODUCTS." 
                                                  WHERE products_id = '".(int)$productId."'");
            $product_image = xtc_db_fetch_array($product_image_query);

            $duplicate_image_query = xtc_db_query("SELECT count(*) AS total 
                                                     FROM " . TABLE_PRODUCTS . " 
                                                    WHERE products_image = '" . xtc_db_input($product_image['products_image']) . "'");
            $duplicate_image = xtc_db_fetch_array($duplicate_image_query);

            if ($duplicate_image['total'] < 2) {
              //xtc_del_image_file($product_image['products_image']);
            }

            //delete more images
            $mo_images_query = xtc_db_query("SELECT * 
                                               FROM ".TABLE_PRODUCTS_IMAGES." 
                                              WHERE products_id = '".(int)$productId."'");
            while ($mo_images_values = xtc_db_fetch_array($mo_images_query)) {
              $duplicate_more_image_query = xtc_db_query("SELECT count(*) AS total 
                                                            FROM ".TABLE_PRODUCTS_IMAGES." 
                                                           WHERE image_name = '".xtc_db_input($mo_images_values['image_name'])."'");
              $duplicate_more_image = xtc_db_fetch_array($duplicate_more_image_query);
              if ($duplicate_more_image['total'] < 2) {
                //xtc_del_image_file($mo_images_values['image_name']);
                xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." WHERE image_id = '".(int)$mo_images_values['image_id']."'");
              }
            }

            xtc_db_query("DELETE FROM ".TABLE_SPECIALS." WHERE products_id = '".(int)$productId."'");
            xtc_db_query("DELETE FROM ".TABLE_PRODUCTS." WHERE products_id = '".(int)$productId."'");
            xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_XSELL." WHERE products_id = '".(int)$productId."'");
            xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_XSELL." WHERE xsell_id = '".(int)$productId."'");
            xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_IMAGES." WHERE products_id = '".(int)$productId."'");
            xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id = '".(int)$productId."'");
            xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_DESCRIPTION." WHERE products_id = '".(int)$productId."'");
    
            xtc_db_query("DELETE pad 
                            FROM ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad
                            JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa 
                                 ON pa.products_attributes_id = pad.products_attributes_id
                                    AND pa.products_id = '".(int)$productId."'");
            xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_ATTRIBUTES." WHERE products_id = '".(int)$productId."'");

            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET." WHERE products_id = '" . (int)$productId . "' OR products_id LIKE '" . (int)$productId . "{%'");
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET_ATTRIBUTES." WHERE products_id = '" . (int)$productId . "' OR products_id LIKE '" . (int)$productId . "{%'");
            xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_TAGS." WHERE products_id = '".(int)$productId."'");

            xtc_db_query("DELETE rd
                            FROM ".TABLE_REVIEWS_DESCRIPTION." rd
                            JOIN ".TABLE_REVIEWS." r
                                 ON r.reviews_id = rd.reviews_id
                                    AND r.products_id = '".(int)$productId."'");
            xtc_db_query("DELETE FROM ".TABLE_REVIEWS." WHERE products_id = '".(int)$productId."'");
    
            if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
              xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_WISHLIST." WHERE products_id = '" . (int)$productId . "' OR products_id LIKE '" . (int)$productId . "{%'");
              xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_WISHLIST_ATTRIBUTES." WHERE products_id = '" . (int)$productId . "' OR products_id LIKE '" . (int)$productId . "{%'");
            }

            $customers_statuses_array = xtc_get_customers_statuses();
            foreach ($customers_statuses_array as $customers_status) {
              xtc_db_query("DELETE FROM ".TABLE_PERSONAL_OFFERS_BY.$customers_status['id']." WHERE products_id = '".(int)$productId."'");
            }
          }
          
          $this->logger->info(sprintf('Product deleted successfully: %s', $productId));
      }

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
          }

          return $this->GetSingleProduct($productId);
      }
      
      /**
       * Read a product by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function GetSingleProduct(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $result = $this->getProductDetails($productId);
          return $result;
      }

      /**
       * Read a Product by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProduct(int $productId, $Exception = true): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $Exception === true) {
              throw new Exception(sprintf('Product not found: %s', $productId));
          } else {
              $product = xtc_db_fetch_array($product_query);
          }

          $result = $this->encode_request($product);
          return $result;
      }

      /**
       * Read a product description by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function GetProductDescription(int $productId, $Exception = true): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_DESCRIPTION."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $Exception === true) {
              throw new Exception(sprintf('Product description not found: %s', $productId));
          } else {
              $description = [];
              $products_description_query = xtc_db_query("SELECT pd.*,
                                                                 l.code
                                                            FROM ".TABLE_PRODUCTS_DESCRIPTION." pd
                                                            JOIN ".TABLE_LANGUAGES." l
                                                                 ON l.languages_id = pd.language_id
                                                           WHERE pd.products_id = '".(int)$productId."'");
              while ($products_description = xtc_db_fetch_array($products_description_query)) {
                  $code = $products_description['code'];
                  unset($products_description['code']);

                  $description[$code] = $products_description;
              }
          }

          $result = $this->encode_request($description);
          return $result;
      }

      /**
       * Read a Product categories by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductCategories(int $productId, $Exception = true): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $Exception === true) {
              throw new Exception(sprintf('Product categories not found: %s', $productId));
          } else {
              $categories = [];
              $products_categories_query = xtc_db_query("SELECT *
                                                           FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                                          WHERE products_id = '".(int)$productId."'
                                                       ORDER BY categories_id");
              while ($products_categories = xtc_db_fetch_array($products_categories_query)) {
                  $categories[] = $products_categories;
              }
          }

          $result = $this->encode_request($categories);
          return $result;
      }

      /**
       * Read a Product images by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductImages(int $productId, $Exception = true): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_IMAGES."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $Exception === true) {
              throw new Exception(sprintf('Product images not found: %s', $productId));
          } else {
              $images = [];
              $product_images_query = xtc_db_query("SELECT *
                                                      FROM ".TABLE_PRODUCTS_IMAGES."
                                                     WHERE products_id = '".(int)$productId."'
                                                  ORDER BY image_nr, image_id");
              while ($product_images = xtc_db_fetch_array($product_images_query)) {
                  $images[] = $product_images;
              }
          }

          $result = $this->encode_request($images);
          return $result;
      }

      /**
       * Read a Product xsell by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductXsell(int $productId, $Exception = true): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_XSELL."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $Exception === true) {
              throw new Exception(sprintf('Product xsell not found: %s', $productId));
          } else {
              $xsell = array();
              $products_xsell_query = xtc_db_query("SELECT *
                                                      FROM ".TABLE_PRODUCTS_XSELL."
                                                     WHERE products_id = '".(int)$productId."'
                                                  ORDER BY sort_order, ID");
              while ($products_xsell = xtc_db_fetch_array($products_xsell_query)) {
                  $xsell[] = $products_xsell;
              }
          }

          $result = $this->encode_request($xsell);
          return $result;
      }

      /**
       * Read a Product attributes by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductAttributes(int $productId, $Exception = true): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $Exception === true) {
              throw new Exception(sprintf('Product attributes not found: %s', $productId));
          } else {
              $attributes = [];
              $products_attributes_query = xtc_db_query("SELECT *
                                                           FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                                                          WHERE products_id = '".(int)$productId."'
                                                       ORDER BY sortorder, products_attributes_id");
              while ($products_attributes = xtc_db_fetch_array($products_attributes_query)) {
                  $attributes[] = $products_attributes;
              }
          }

          $result = $this->encode_request($attributes);
          return $result;
      }

      /**
       * Read a Product tags by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductTags(int $productId, $Exception = true): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_TAGS."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $Exception === true) {
              throw new Exception(sprintf('Product tags not found: %s', $productId));
          } else {
              $tags = [];
              $products_tags_query = xtc_db_query("SELECT *
                                                     FROM ".TABLE_PRODUCTS_TAGS."
                                                    WHERE products_id = '".(int)$productId."'
                                                 ORDER BY sort_order, products_tags_id");
              while ($products_tags = xtc_db_fetch_array($products_tags_query)) {
                  $tags[] = $products_tags;
              }
          }

          $result = $this->encode_request($tags);
          return $result;
      }

      /**
       * Read a Product tags by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductSpecials(int $productId, $Exception = true): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_SPECIALS."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $Exception === true) {
              throw new Exception(sprintf('Product specials not found: %s', $productId));
          } else {
              $specials = [];
              $products_specials_query = xtc_db_query("SELECT *
                                                         FROM ".TABLE_SPECIALS."
                                                        WHERE products_id = '".(int)$productId."'");
              while ($products_specials = xtc_db_fetch_array($products_specials_query)) {
                  $specials[] = $products_specials;
              }
          }

          $result = $this->encode_request($specials);
          return $result;
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
                      $products_description = xtc_db_fetch_array($products_description_query);

                      foreach ($products_description as $key => $value) {
                          if (isset($this->options[$languages['code']][$key])) {
                              $products_description[$key] = $this->options[$languages['code']][$key];
                          }
                      }

                      // Input validation
                      $this->checkTableData(TABLE_PRODUCTS_DESCRIPTION, $products_description);
                      xtc_db_perform(TABLE_PRODUCTS_DESCRIPTION, $products_description, 'update', "products_id = '".(int)$productId."' AND language_id = '".(int)$languages['languages_id']."'");
                  } elseif (isset($this->options[$languages['code']])) {
                      $products_description = $this->getDefaultTableValues(TABLE_PRODUCTS_DESCRIPTION);
                      $products_description['products_id'] = (int)$productId;
                      $products_description['language_id'] = (int)$languages['languages_id'];

                      foreach ($products_description as $key => $value) {
                          if (isset($this->options[$languages['code']][$key])) {
                              $products_description[$key] = $this->options[$languages['code']][$key];
                          }
                      }

                      // Input validation
                      $this->checkTableData(TABLE_PRODUCTS_DESCRIPTION, $products_description);
                      xtc_db_perform(TABLE_PRODUCTS_DESCRIPTION, $products_description);
                  }
              }
          }

          return $this->getProductDescription($productId);
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
