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
  trait CategoryGetAction
  {
      /**
       * Read a category by the given category id.
       *
       * @param int $categoryId The category id
       *
       * @throws Exception
       *
       * @return array The category data
       */
      public function GetCategoryDetails(int $categoryId): array
      {
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }
          
          $category_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CATEGORIES."
                                           WHERE categories_id = '".(int)$categoryId."'");
          if (xtc_db_num_rows($category_query) < 1) {
              $this->errormessage(sprintf('Category not found: %s', $categoryId));
          } else {
              // disable Exception
              $this->throw_exception = false;

              $result = [
                  'categories' => $this->GetCategory($categoryId),
                  'categories_description' => $this->GetCategoryDescription($categoryId),
              ];         
              
              if (isset($this->options['with'])) {
                  $with = explode(',', $this->options['with']);
                  if (in_array('products', $with) !== false) {
                      $result['products_to_categories'] = $this->GetCategoryProducts($categoryId);
                  }
              }
              
              return $result;
          }
      }

      /**
       * Read categorys by given conditions
       *
       * @param mixed[] $options
       *
       * @return array The category data
       */
      public function GetCategories(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
          
          $conditions = [];
          if (isset($this->options['status']) && preg_replace('/[^\d\,]/', '', $this->options['status']) != '') {
              $conditions[] = " categories_status IN (".preg_replace('/[^\d\,]/', '', $this->options['status']).") ";
          }
          if (isset($this->options['parent']) && preg_replace('/[^\d\,]/', '', $this->options['parent']) != '') {
              $conditions[] = " parent_id IN (".preg_replace('/[^\d\,]/', '', $this->options['parent']).") ";
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
                                              
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_CATEGORIES."
                                              ".$where);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              $this->errormessage('no Category found');
          }
          
          $data = [];
          $categories_query = xtc_db_query("SELECT categories_id
                                              FROM ".TABLE_CATEGORIES."
                                                   ".$where."
                                          ORDER BY categories_id ASC
                                             LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($categories = xtc_db_fetch_array($categories_query)) {
              $data[] = $this->GetCategoryDetails($categories['categories_id']);
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
       * Read a category by the given category id.
       *
       * @param int $categoryId The category id
       *
       * @throws Exception
       *
       * @return array The category data
       */
      public function GetSingleCategory(int $categoryId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }

          $result = $this->GetCategoryDetails($categoryId);
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
          if (xtc_db_num_rows($category_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Category not found: %s', $categoryId));
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
          if (xtc_db_num_rows($category_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Category description not found: %s', $categoryId));
          } else {
              $description = [];
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
       * Read a category products by the given category id.
       *
       * @param int $categoryId The category id
       *
       * @throws Exception
       *
       * @return array The category data
       */
      public function GetCategoryProducts(int $categoryId): array
      {
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }
          
          $products = [];
          $category_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CATEGORIES."
                                           WHERE categories_id = '".(int)$categoryId."'");
          if (xtc_db_num_rows($category_query) < 1 && $this->throw_exception === true) {
              $this->errormessage(sprintf('Category products not found: %s', $categoryId));
          } else {
              $categories_products_query = xtc_db_query("SELECT *
                                                           FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                                          WHERE categories_id = '".(int)$categoryId."'
                                                       ORDER BY products_id");
              while ($categories_products = xtc_db_fetch_array($categories_products_query)) {
                  $products[] = $categories_products;
              }
          }

          $result = $this->encode_request($products);
          return $result;
      }
    
  }
