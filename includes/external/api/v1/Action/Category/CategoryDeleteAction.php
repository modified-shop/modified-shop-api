<?php

/**
 * /includes/external/api/v1/Action/Category/CategoryDeleteAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Category;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  trait CategoryDeleteAction
  {
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
              return $this->errormessage(sprintf('Category not found: %s', $categoryId));
          } else {
              $subcategories_query = xtc_db_query("SELECT *
                                                     FROM ".TABLE_CATEGORIES."
                                                    WHERE parent_id = '".(int)$categoryId."'");
              $count = xtc_db_num_rows($subcategories_query);
              if ($count > 0) {
                  return $this->errormessage(sprintf('Category can not get deleted due to connected categories: %s', $count), 400);
              } else {
                  $products_query = xtc_db_query("SELECT *
                                                    FROM ".TABLE_PRODUCTS_TO_CATEGORIES." 
                                                   WHERE categories_id = '".(int)$categoryId."'");
                  $count = xtc_db_num_rows($products_query);
                  if ($count > 0) {
                      return $this->errormessage(sprintf('Category can not get deleted due to connected products: %s', $count), 400);
                  } else {
                      // disable Exception
                      $this->throw_exception = false;

                      $this->DeleteImages($categoryId);
                      $this->DeleteAllProducts($categoryId);
                      
                      xtc_db_query("DELETE FROM ".TABLE_CATEGORIES." WHERE categories_id = '".(int)$categoryId."'");
                      xtc_db_query("DELETE FROM ".TABLE_CATEGORIES_DESCRIPTION." WHERE categories_id = '".(int)$categoryId."'");
                      
                      $this->logger->info(sprintf('Category deleted successfully: %s', $categoryId));
                  }
              }
          }          
      }

      /**
       * Delete a product by the given category id and product id.
       *
       * @param int $categoryId The category id
       * @param int $productId The product id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteProduct(int $categoryId, int $productId): void
      {
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }

          $where = '';
          if ($productId > 0) {
              $where = "AND products_id = '".(int)$productId."'";
          }

          $category_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                           WHERE categories_id = '".(int)$categoryId."'
                                                 ".$where);
          if (xtc_db_num_rows($category_query) < 1 && $this->throw_exception === true) {
              return $this->errormessage(sprintf('Category products not found: %s', $categoryId));
          } else {
              while ($category = xtc_db_fetch_array($category_query)) {
                  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_TO_CATEGORIES." 
                                      WHERE categories_id = '".(int)$categoryId."'
                                        AND products_id = '".(int)$category['products_id']."'");
              }
          }
      }

      /**
       * Delete all product by the given category id.
       *
       * @param int $categoryId The category id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteAllProducts(int $categoryId): void
      {
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }

          $this->DeleteProduct($categoryId, 0);
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
              return $this->errormessage(sprintf('Category not found: %s', $categoryId));
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

  }
