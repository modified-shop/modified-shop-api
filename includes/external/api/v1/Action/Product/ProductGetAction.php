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
  
  /**
   * Service.
   */
  trait ProductGetAction
  {
      /**
       * Read an product by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function GetProductDetails(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }
          
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1) {
              $this->errormessage(sprintf('Product not found: %s', $productId));
          } else {
              // disable Exception
              $this->throw_exception = false;
              
              $result = [
                  'products' => $this->GetProduct($productId, false),
                  'products_description' => $this->GetProductDescription($productId),
              ];
              
              if (isset($this->options['with'])) {
                  $with = explode(',', $this->options['with']);
                  if (in_array('categories', $with) !== false) {
                      $result['products_to_categories'] = $this->GetProductCategories($productId);
                  }
                  if (in_array('images', $with) !== false) {
                      $result['products_images'] = $this->GetProductImages($productId);
                  }
                  if (in_array('xsell', $with) !== false) {
                      $result['products_xsell'] = $this->GetProductXsell($productId);
                  }
                  if (in_array('attributes', $with) !== false) {
                      $result['products_attributes'] = $this->GetProductAttributes($productId);
                  }
                  if (in_array('tags', $with) !== false) {
                      $result['products_tags'] = $this->GetProductTags($productId);
                  }
                  if (in_array('content', $with) !== false) {
                      $result['products_content'] = $this->GetProductContent($productId);
                  }
                  if (in_array('offer', $with) !== false) {
                      $result['personal_offer'] =  $this->GetProductPersonalOffer($productId);
                  }
                  if (in_array('specials', $with) !== false) {
                      $result['specials'] = $this->GetProductSpecials($productId);
                  }
                  if (in_array('reviews', $with) !== false) {
                      $result['reviews'] = $this->GetProductReviews($productId);
                  }
              }

              return $result;
          }
      }

      /**
       * Read a product by the given product id.
       *
       * @param int $productId The product id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The product data
       */
      public function GetSingleProduct(int $productId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $result = $this->GetProductDetails($productId);
          return $result;
      }

      /**
       * Read products by given conditions
       *
       * @param mixed[] $options
       *
       * @return array The product data
       */
      public function GetProducts(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
                    
          $conditions = [];
          if (isset($this->options['status']) && !empty(preg_replace('/[^\d\,]/', '', $this->options['status']))) {
              $conditions[] = " products_status IN (".preg_replace('/[^\d\,]/', '', $this->options['status']).") ";
          }
          if ((int)$this->options['from'] > 0) {
              $conditions[] = " products_date_added >= '".date('Y-m-d H:i:s', (int)$this->options['from'])."' ";
          }
          if ((int)$this->options['to'] > 0) {
              $conditions[] = " products_date_added <= '".date('Y-m-d H:i:s', (int)$this->options['to'])."' ";
          }
          
          $where = '';
          if (count($conditions) > 0) {
              $where = " WHERE ".implode(' AND ', $conditions);
          }
          
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_PRODUCTS."
                                              ".$where);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              $this->errormessage('no Product found');
          }
          
          $data = [];
          $products_query = xtc_db_query("SELECT products_id
                                            FROM ".TABLE_PRODUCTS."
                                                 ".$where."
                                        ORDER BY products_date_added DESC, products_id ASC
                                           LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($products = xtc_db_fetch_array($products_query)) {
              $data[] = $this->GetProductDetails($products['products_id']);
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
       * Read a Product by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProduct(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $product = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product not found: %s', $productId));
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
      public function GetProductDescription(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $description = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_DESCRIPTION."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product description not found: %s', $productId));
          } else {
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
      public function GetProductCategories(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $categories = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product categories not found: %s', $productId));
          } else {
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
      public function GetProductImages(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $images = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_IMAGES."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product images not found: %s', $productId));
          } else {
              $product_images_query = xtc_db_query("SELECT *
                                                      FROM ".TABLE_PRODUCTS_IMAGES."
                                                     WHERE products_id = '".(int)$productId."'
                                                  ORDER BY image_nr, image_id");
              while ($product_images = xtc_db_fetch_array($product_images_query)) {
                  $this->throw_exception = false;
                  $product_images = array_merge($product_images, $this->GetProductImagesDescription($productId, $product_images['image_id']));

                  $images[] = $product_images;
              }
          }

          $result = $this->encode_request($images);
          return $result;
      }

      /**
       * Read a Product image description by the given Product id and image id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductImagesDescription(int $productId, int $imageId = 0): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $where = '';
          if ($imageId > 0) {
              $where = "AND pid.image_id = '".(int)$imageId."'";
          }

          $description = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_IMAGES."
                                          WHERE products_id = '".(int)$productId."'
                                                ".str_replace('pid.', '', $where));
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product images not found: %s', $productId));
          } else {
              $image_description_query = xtc_db_query("SELECT pid.*,
                                                              l.code
                                                         FROM ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." pid
                                                         JOIN ".TABLE_LANGUAGES." l
                                                              ON l.languages_id = pid.language_id
                                                        WHERE pid.products_id = '".(int)$productId."'
                                                              ".$where);
              while ($image_description = xtc_db_fetch_array($image_description_query)) {
                  $code = $image_description['code'];
                  unset($image_description['code']);

                  $description[$code] = $image_description;
              }
          }

          $result = $this->encode_request($description);
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
      public function GetProductXsell(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $xsell = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_XSELL."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product xsell not found: %s', $productId));
          } else {
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
      public function GetProductAttributes(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $attributes = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product attributes not found: %s', $productId));
          } else {
              $products_attributes_query = xtc_db_query("SELECT *
                                                           FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                                                          WHERE products_id = '".(int)$productId."'
                                                       ORDER BY sortorder, products_attributes_id");
              while ($products_attributes = xtc_db_fetch_array($products_attributes_query)) {
                  $attributes_download_query = xtc_db_query("SELECT *
                                                               FROM ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD."
                                                              WHERE products_attributes_id = '".(int)$products_attributes['products_attributes_id']."'");
                  if (xtc_db_num_rows($attributes_download_query) > 0) {
                    $products_attributes['attributes_downloads'] = xtc_db_fetch_array($attributes_download_query);
                  }
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
      public function GetProductTags(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $tags = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_TAGS."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product tags not found: %s', $productId));
          } else {
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
       * Read a Product content by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductContent(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $content = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_CONTENT."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product tags not found: %s', $productId));
          } else {
              $products_content_query = xtc_db_query("SELECT *
                                                     FROM ".TABLE_PRODUCTS_CONTENT."
                                                    WHERE products_id = '".(int)$productId."'
                                                 ORDER BY sort_order, content_id");
              while ($products_content = xtc_db_fetch_array($products_content_query)) {
                  $content[] = $products_content;
              }
          }

          $result = $this->encode_request($content);
          return $result;
      }

      /**
       * Read a Product specials by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductSpecials(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $specials = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_SPECIALS."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product specials not found: %s', $productId));
          } else {
              $products_specials_query = xtc_db_query("SELECT *
                                                         FROM ".TABLE_SPECIALS."
                                                        WHERE products_id = '".(int)$productId."'
                                                     ORDER BY specials_id ASC");
              while ($products_specials = xtc_db_fetch_array($products_specials_query)) {
                  $specials[] = $products_specials;
              }
          }

          $result = $this->encode_request($specials);
          return $result;
      }

      /**
       * Read a Product reviews by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductReviews(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $reviews = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_REVIEWS."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product reviews not found: %s', $productId));
          } else {
              $products_reviews_query = xtc_db_query("SELECT *
                                                        FROM ".TABLE_REVIEWS." r
                                                        JOIN ".TABLE_REVIEWS_DESCRIPTION." rd
                                                             ON r.reviews_id = rd.reviews_id
                                                       WHERE r.products_id = '".(int)$productId."'
                                                    ORDER BY r.date_added DESC, r.reviews_id ASC");
              while ($products_reviews = xtc_db_fetch_array($products_reviews_query)) {
                  $reviews[] = $products_reviews;
              }
          }

          $result = $this->encode_request($reviews);
          return $result;
      }

      /**
       * Read a Product personal offer by the given Product id.
       *
       * @param int $productId The Product id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductPersonalOffer(int $productId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $personal_offer = [];
          $customers_statuses_array = xtc_get_customers_statuses();
          foreach ($customers_statuses_array as $customers_status) {
              $offer = [];
              $products_personal_offer_query = xtc_db_query("SELECT *
                                                               FROM ".TABLE_PERSONAL_OFFERS_BY.$customers_status['id']."
                                                              WHERE products_id = '".(int)$productId."'
                                                           ORDER BY price_id ASC");
              while ($products_personal_offer = xtc_db_fetch_array($products_personal_offer_query)) {
                  $offer[] = $products_personal_offer;
              }

              $personal_offer[] = [
                'status_id' => $customers_status['id'],
                'data' => $offer,
              ];
          }

          $result = $this->encode_request($personal_offer);
          return $result;
      }

      /**
       * Read a Product personal offer by the given Product id and Status id.
       *
       * @param int $productId The Product id
       * @param int $statusId The Status id
       *
       * @throws Exception
       *
       * @return array The Product data
       */
      public function GetProductPersonalOfferByStatus(int $productId, int $statusId): array
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }
          if (!xtc_not_null($statusId)) {
              throw new Exception('Status ID required');
          }

          $personal_offer = [];
          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PERSONAL_OFFERS_BY.$statusId."
                                          WHERE products_id = '".(int)$productId."'");
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Product personal offer not found: %s', $productId));
          } else {
              $products_personal_offer_query = xtc_db_query("SELECT *
                                                               FROM ".TABLE_PERSONAL_OFFERS_BY.$statusId."
                                                              WHERE products_id = '".(int)$productId."'
                                                           ORDER BY price_id ASC");
              while ($products_personal_offer = xtc_db_fetch_array($products_personal_offer_query)) {
                  $personal_offer[] = $products_personal_offer;
              }
          }

          $result = $this->encode_request($personal_offer);
          return $result;
      }

  }
