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
  trait ProductDeleteAction
  {
      /**
       * Delete a product by the given product id.
       *
       * @param int $productId The product id
       *
       * @return void
       */
      public function DeleteProduct(int $productId): void
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
              // disable Exception
              $this->throw_exception = false;

              //delete details
              $this->DeleteImage($productId);
              $this->DeleteAllImages($productId);
              $this->DeleteAllXsell($productId);
              $this->DeleteAllSpecials($productId);
              $this->DeleteAllAttributes($productId);
              $this->DeleteAllTags($productId);
              $this->DeleteAllCategory($productId);
              $this->DeleteAllContents($productId);
              $this->DeleteAllReviews($productId);
              $this->DeleteAllPersonalOffers($productId);

              //delete
              xtc_db_query("DELETE FROM ".TABLE_PRODUCTS." WHERE products_id = '".(int)$productId."'");
              xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_DESCRIPTION." WHERE products_id = '".(int)$productId."'");

              //delete cart
              xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET." WHERE products_id = '" . (int)$productId . "' OR products_id LIKE '" . (int)$productId . "{%'");
              xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET_ATTRIBUTES." WHERE products_id = '" . (int)$productId . "' OR products_id LIKE '" . (int)$productId . "{%'");

              //delete wishlist
              if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
                  xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_WISHLIST." WHERE products_id = '" . (int)$productId . "' OR products_id LIKE '" . (int)$productId . "{%'");
                  xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_WISHLIST_ATTRIBUTES." WHERE products_id = '" . (int)$productId . "' OR products_id LIKE '" . (int)$productId . "{%'");
              }
          }
          
          $this->logger->info(sprintf('Product deleted successfully: %s', $productId));
      }

      /**
       * Delete a category by the given product id and category id.
       *
       * @param int $productId The product id
       * @param int $categoryId The category id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteCategory(int $productId, int $categoryId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $where = '';
          if ($categoryId > 0) {
              $where = "AND categories_id = '".(int)$categoryId."'";
          }

          $product_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                          WHERE products_id = '".(int)$productId."'
                                                ".$where);
          if (xtc_db_num_rows($product_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Product categories not found: %s', $productId));
          } else {
              while ($product = xtc_db_fetch_array($product_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_TO_CATEGORIES." 
                                      WHERE products_id = '".(int)$productId."'
                                        AND categories_id = '".(int)$product['categories_id']."'");
              }
          }
      }

      /**
       * Delete all category by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllCategory(int $productId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $this->DeleteCategory($productId, 0);
      }

      /**
       * Delete a image by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteImage(int $productId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }
          
          $images_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_PRODUCTS."
                                         WHERE products_id = '".(int)$productId."'
                                           AND products_image != ''");
          if (xtc_db_num_rows($images_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Product image not found: %s', $productId));
          } else {
              $images = xtc_db_fetch_array($images_query);
              
              $this->deleteImageFile($images['products_image']);
              
              xtc_db_query("UPDATE ".TABLE_PRODUCTS." 
                               SET products_image = ''
                             WHERE products_id = '".(int)$productId."'");
          }
      }

      /**
       * Delete a image by the given product id and image id.
       *
       * @param int $productId The product id
       * @param int $imageId The image id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteImages(int $productId, int $imageId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }
          
          $where = '';
          if ($imageId > 0) {
              $where = "AND image_id = '".(int)$imageId."'";
          }
          
          $images_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_PRODUCTS_IMAGES."
                                         WHERE products_id = '".(int)$productId."'
                                             ".$where);
          if (xtc_db_num_rows($images_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Product more images not found: %s', $productId));
          } else {
              while ($images = xtc_db_fetch_array($images_query)) {
                  // disable Exception
                  $this->throw_exception = false;

                  $this->deleteImageFile($images['image_name']);                  
                  $this->DeleteImagesDescription($productId, $images['image_id']);
                  
                  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_IMAGES." 
                                      WHERE products_id = '".(int)$productId."'
                                        AND image_id = '".(int)$images['image_id']."'");
              }
          }
      }

      /**
       * Delete all image by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllImages(int $productId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }
          
          $this->DeleteImages($productId, 0);
      }

      /**
       * Delete a image description by the given product id and image id.
       *
       * @param int $productId The product id
       * @param int $imageId The image id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteImagesDescription(int $productId, int $imageId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }
          
          $where = '';
          if ($imageId > 0) {
              $where = "AND image_id = '".(int)$imageId."'";
          }
          
          $images_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_PRODUCTS_IMAGES."
                                         WHERE products_id = '".(int)$productId."'
                                             ".$where);
          if (xtc_db_num_rows($images_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Product more images not found: %s', $productId));
          } else {
              while ($images = xtc_db_fetch_array($images_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." 
                                      WHERE products_id = '".(int)$productId."'
                                        AND image_id = '".(int)$images['image_id']."'");
              }
          }
      }

      /**
       * Delete a xsell by the given product id and xsell id.
       *
       * @param int $productId The product id
       * @param int $xsellId The xsell id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteXsell(int $productId, int $xsellId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $where = '';
          if ($xsellId > 0) {
              $where = "AND ID = '".(int)$xsellId."'";
          }

          $xsell_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_PRODUCTS_XSELL."
                                        WHERE products_id = '".(int)$productId."'
                                              ".$where);
          if (xtc_db_num_rows($xsell_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Product xsell not found: %s', $productId));
          } else {
              while ($xsell = xtc_db_fetch_array($xsell_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_XSELL." 
                                      WHERE products_id = '".(int)$productId."'
                                        AND ID = '".(int)$xsell['ID']."'");
              }
          }
      }

      /**
       * Delete all xsell by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllXsell(int $productId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $this->DeleteXsell($productId, 0);
      }

      /**
       * Delete a special by the given product id and specials id.
       *
       * @param int $productId The product id
       * @param int $specialsId The specials id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteSpecials(int $productId, int $specialsId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $where = '';
          if ($specialsId > 0) {
              $where = "AND specials_id = '".(int)$specialsId."'";
          }

          $specials_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_SPECIALS."
                                           WHERE products_id = '".(int)$productId."'
                                                 ".$where);
          if (xtc_db_num_rows($specials_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Product specials not found: %s', $productId));
          } else {
              while ($specials = xtc_db_fetch_array($specials_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_SPECIALS." 
                                      WHERE products_id = '".(int)$productId."'
                                        AND specials_id = '".(int)$specials['specials_id']."'");
              }
          }
      }

      /**
       * Delete a special by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllSpecials(int $productId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $this->DeleteSpecials($productId, 0);
      }

      /**
       * Delete an attribute by the given product id and attributes id.
       *
       * @param int $productId The product id
       * @param int $attributesId The attributes id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAttributes(int $productId, int $attributesId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $where = '';
          if ($attributesId > 0) {
              $where = "AND products_attributes_id = '".(int)$attributesId."'";
          }

          $attributes_query = xtc_db_query("SELECT *
                                              FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                                             WHERE products_id = '".(int)$productId."'
                                                   ".$where);
          if (xtc_db_num_rows($attributes_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Product attributes not found: %s', $productId));
          } else {
              while ($attributes = xtc_db_fetch_array($attributes_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_ATTRIBUTES." 
                                      WHERE products_id = '".(int)$productId."'
                                        AND products_attributes_id = '".(int)$attributes['products_attributes_id']."'");

                  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." 
                                      WHERE products_attributes_id = '".(int)$attributes['products_attributes_id']."'");
              }
          }
      }

      /**
       * Delete all attributes by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllAttributes(int $productId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $this->DeleteAttributes($productId, 0);
      }

      /**
       * Delete a tag by the given product id and tags id.
       *
       * @param int $productId The product id
       * @param int $tagsId The tags id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteTags(int $productId, int $tagsId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $where = '';
          if ($tagsId > 0) {
              $where = "AND products_tags_id = '".(int)$tagsId."'";
          }

          $tags_query = xtc_db_query("SELECT *
                                        FROM ".TABLE_PRODUCTS_TAGS."
                                       WHERE products_id = '".(int)$productId."'
                                             ".$where);
          if (xtc_db_num_rows($tags_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Product tags not found: %s', $productId));
          } else {
              while ($tags = xtc_db_fetch_array($tags_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_TAGS." 
                                      WHERE products_id = '".(int)$productId."'
                                        AND products_tags_id = '".(int)$tags['products_tags_id']."'");
              }
          }
      }

      /**
       * Delete all tags by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllTags(int $productId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $this->DeleteTags($productId, 0);
      }

      /**
       * Delete a content by the given product id and content id.
       *
       * @param int $productId The product id
       * @param int $contentId The content id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteContents(int $productId, int $contentId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $where = '';
          if ($contentId > 0) {
              $where = "AND content_id = '".(int)$contentId."'";
          }

          $content_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS_CONTENT."
                                          WHERE products_id = '".(int)$productId."'
                                                ".$where);
          if (xtc_db_num_rows($content_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Product content not found: %s', $productId));
          } else {
              while ($content = xtc_db_fetch_array($content_query)) {
                  $duplicate_content_query = xtc_db_query("SELECT COUNT(*) AS total 
                                                             FROM ".TABLE_PRODUCTS_CONTENT." 
                                                            WHERE content_file = '".xtc_db_input($content['content_file'])."' 
                                                              AND products_id != '".(int)$productId."'");
                  $duplicate_content = xtc_db_fetch_array($duplicate_content_query);
                  if ($duplicate_content['total'] == 0
                      && is_file(DIR_FS_CATALOG.'media/products/'.$content['content_file'])
                      )
                  {
                     unlink(DIR_FS_CATALOG.'media/products/'.$content['content_file']);
                  }

                  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_CONTENT." 
                                      WHERE products_id = '".(int)$productId."'
                                        AND content_id = '".(int)$content['content_id']."'");
              }
          }
      }

      /**
       * Delete all contents by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllContents(int $productId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $this->DeleteContents($productId, 0);
      }

      /**
       * Delete a personal offer by the given product id, status id and price id.
       *
       * @param int $productId The product id
       * @param int $statusId The status id
       * @param int $priceId The price id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeletePersonalOffer(int $productId, int $statusId, int $priceId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }
          if (!xtc_not_null($statusId)) {
              throw new Exception('Status ID required');
          }

          $where = '';
          if ($priceId > 0) {
              $where = "AND price_id = '".(int)$priceId."'";
          }

          $personal_offer_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_PERSONAL_OFFERS_BY.$statusId."
                                                 WHERE products_id = '".(int)$productId."'
                                                       ".$where);
          if (xtc_db_num_rows($personal_offer_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Product personal offer not found: %s', $productId));
          } else {
              while ($personal_offer = xtc_db_fetch_array($personal_offer_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_PERSONAL_OFFERS_BY.$statusId."
                                      WHERE products_id = '".(int)$productId."'
                                        AND price_id = '".(int)$personal_offer['price_id']."'");
              }
          }
      }

      /**
       * Delete all personal offer by the given product id and status id.
       *
       * @param int $productId The product id
       * @param int $statusId The status id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllPersonalOffer(int $productId, int $statusId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }
          if (!xtc_not_null($statusId)) {
              throw new Exception('Status ID required');
          }

          $this->DeletePersonalOffer($productId, $statusId, 0);
      }

      /**
       * Delete all personal offer by the given product id.
       *
       * @param int $productId The product id
       *       *
       * @return void
       */
      public function DeleteAllPersonalOffers(int $productId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          // disable Exception
          $this->throw_exception = false;

          $customers_statuses_array = xtc_get_customers_statuses();
          foreach ($customers_statuses_array as $customers_status) {
              $this->DeleteAllPersonalOffer($productId, $customers_status['id']);
          }
      }

      /**
       * Delete a review by the given product id and reviews id.
       *
       * @param int $productId The product id
       * @param int $reviewsId The reviews id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteReviews(int $productId, int $reviewsId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $where = '';
          if ($reviewsId > 0) {
              $where = "AND reviews_id = '".(int)$reviewsId."'";
          }

          $reviews_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_REVIEWS."
                                          WHERE products_id = '".(int)$productId."'
                                                ".$where);
          if (xtc_db_num_rows($reviews_query) < 1 && $this->throw_exception === true) {
              throw new Exception(sprintf('Product personal offer not found: %s', $productId));
          } else {
              while ($reviews = xtc_db_fetch_array($reviews_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_REVIEWS."
                                      WHERE products_id = '".(int)$productId."'
                                        AND reviews_id = '".(int)$reviews['reviews_id']."'");

                  xtc_db_query("DELETE FROM ".TABLE_REVIEWS_DESCRIPTION."
                                      WHERE reviews_id = '".(int)$reviews['reviews_id']."'");
              }
          }
      }

      /**
       * Delete all reviews by the given product id.
       *
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllReviews(int $productId): void
      {
          // Input validation
          if (empty($productId)) {
              throw new Exception('Product ID required');
          }

          $this->DeleteReviews($productId, 0);
      }

      /**
       * Deletes an image by given name.
       *
       * @param string $image_name
       *
       * @throws Exception
       *
       * @return void
       */
      private function deleteImageFile($image_name): void
      {
          $total = 0;
          $product_image_query = xtc_db_query("SELECT COUNT(*) AS total 
                                                 FROM ".TABLE_PRODUCTS." 
                                                WHERE products_image = '".xtc_db_input($image_name)."'");
          $product_image = xtc_db_fetch_array($product_image_query);
          $total += $product_image['total'];

          $more_image_query = xtc_db_query("SELECT COUNT(*) AS total 
                                              FROM ".TABLE_PRODUCTS_IMAGES." 
                                             WHERE image_name = '".xtc_db_input($image_name)."'");
          $more_image = xtc_db_fetch_array($more_image_query);
          $total += $more_image['total'];

          if ($total < 2) {
              //xtc_del_image_file($image_name);
          }
      }

  }
