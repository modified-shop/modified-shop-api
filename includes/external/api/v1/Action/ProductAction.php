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
  final class ProductAction
  {
      /**
       * @var mixed[]
       */
      private $options = [
          "status" => null,
          "from" => null,
          "to" => null,
          "page" => 1,
          "limit" => 10,
      ];

      /**
       * @var LoggerInterface
       */
      private $logger;

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
            $product = xtc_db_fetch_array($product_query);
            
            $description = array();
            $products_description_query = xtc_db_query("SELECT pd.*,
                                                               l.code
                                                          FROM ".TABLE_PRODUCTS_DESCRIPTION." pd
                                                          JOIN ".TABLE_LANGUAGES." l
                                                         WHERE products_id = '".(int)$productId."'");
            while ($products_description = xtc_db_fetch_array($products_description_query)) {
              $code = $products_description['code'];
              unset($products_description['code']);
              
              $description[$code] = $products_description;
            }

            $images = array();
            $products_images_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_PRODUCTS_IMAGES."
                                                   WHERE products_id = '".(int)$productId."'
                                                ORDER BY image_nr, image_id");
            while ($products_images = xtc_db_fetch_array($products_images_query)) {
              $images[] = $products_images;
            }

            $attributes = array();
            $products_attributes_query = xtc_db_query("SELECT *
                                                         FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                                                        WHERE products_id = '".(int)$productId."'
                                                     ORDER BY sortorder, products_attributes_id");
            while ($products_attributes = xtc_db_fetch_array($products_attributes_query)) {
              $attributes[] = $products_attributes;
            }

            $tags = array();
            $products_tags_query = xtc_db_query("SELECT *
                                                   FROM ".TABLE_PRODUCTS_TAGS."
                                                  WHERE products_id = '".(int)$productId."'
                                               ORDER BY sort_order, products_tags_id");
            while ($products_tags = xtc_db_fetch_array($products_tags_query)) {
              $tags[] = $products_tags;
            }

            $xsell = array();
            $products_xsell_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_PRODUCTS_XSELL."
                                                   WHERE products_id = '".(int)$productId."'
                                                ORDER BY sort_order, ID");
            while ($products_xsell = xtc_db_fetch_array($products_xsell_query)) {
              $xsell[] = $products_xsell;
            }

            $categories = array();
            $products_categories_query = xtc_db_query("SELECT *
                                                         FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                                        WHERE products_id = '".(int)$productId."'
                                                     ORDER BY categories_id");
            while ($products_categories = xtc_db_fetch_array($products_categories_query)) {
              $categories[] = $products_categories;
            }
          }
          
          $result = [
            'products' => $product,
            'products_description' => $description,
            'products_to_categories' => $categories,
            'products_images' => $images,
            'products_attributes' => $attributes,
            'products_tags' => $tags,
            'products_xsell' => $xsell,
          ];
          
          $result = $this->encode_request($result);
          return $result;
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
                                                     FROM ".TABLE_PRODUCTS." 
                                                    WHERE products_image = '".xtc_db_input($product_image['products_image'])."'");
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
       * encoding given array or string.
       *
       * @param mixed[] $data
       *
       * @return mixed
       */
      function encode_request($string) {
          if (is_array($string)) {
              foreach ($string as $key => $value) {
                  $string[$key] = $this->encode_request($value);
              }
          } else {
              if (!is_bool($string)) {
                  $string = decode_htmlentities($string);
                  $cur_encoding = mb_detect_encoding($string);
                  if ($cur_encoding == "UTF-8" && mb_check_encoding($string, "UTF-8")) {
                      return $string;
                  } else {
                      return mb_convert_encoding($string, "UTF-8", $_SESSION['language_charset']);
                  }
              }
          }
    
          return $string;  
      }

      /**
       * Hydrate options from given array.
       *
       * @param mixed[] $data
       *
       * @return void
       */
      private function hydrate(array $data = []): void
      {
          foreach ($data as $key => $value) {
              /* https://github.com/facebook/hhvm/issues/6368 */
              $key = str_replace(".", " ", $key);
              $method = lcfirst(ucwords($key));
              $method = str_replace(" ", "", $method);
              if (method_exists($this, $method)) {
                  /* Try to use setter */
                  /** @phpstan-ignore-next-line */
                  call_user_func([$this, $method], $value);
              } else {
                  /* Or fallback to setting option directly */
                  $this->options[$key] = $value;
              }
          }
      }
  }
