<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Tags;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;

  /**
   * Service.
   */
  final class TagsAction extends BaseAction
  {
      use TagsGetAction;
      use TagsDeleteAction;

      /**
       * Insert an option
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The option data
       */
      public function InsertOption(array $options): array
      {
          $option = $this->InsertUpdateOption(0, $options);
          
          return $option;
      }
      
      /**
       * Insert or Update an option by given option id.
       *
       * @param int $optionId The option id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The option data
       */
      public function InsertUpdateOption(int $optionId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $languages_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_LANGUAGES);
          while ($languages = xtc_db_fetch_array($languages_query)) {
              $where = '';
              if ($optionId > 0) {
                  $where = "AND options_id = '".(int)$optionId."'";
                  $option_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_PRODUCTS_TAGS_OPTIONS."
                                                 WHERE languages_id = '".(int)$languages['languages_id']."'
                                                       ".$where);
                  if (xtc_db_num_rows($option_query) < 1) {
                      $action = 'insert';
                      $option = $this->getDefaultTableValues(TABLE_PRODUCTS_TAGS_OPTIONS);
                      $option['options_id'] = $optionId;
                      $option['languages_id'] = (int)$languages['languages_id'];
                  } else {
                      $action = 'update';
                      $option = xtc_db_fetch_array($option_query);
                  }
              } else {
                  $action = 'insert';
                  $option = $this->getDefaultTableValues(TABLE_PRODUCTS_TAGS_OPTIONS);
                  
                  if ($optionId < 1) {
                      $next_id_query = xtc_db_query("SELECT max(options_id) as options_id 
                                                       FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . "");
                      $next_id = xtc_db_fetch_array($next_id_query);
                      $optionId = $next_id['options_id'] + 1;
                  }

                  $option['options_id'] = $optionId;
                  $option['language_id'] = (int)$languages['languages_id'];
              }
    
              foreach ($option as $key => $value) {
                  if (isset($this->options[$languages['code']][$key])) {
                      $option[$key] = $this->options[$languages['code']][$key];
                  }
              }
    
              // Input validation
              $this->checkTableData(TABLE_PRODUCTS_TAGS_OPTIONS, $option);
              xtc_db_perform(TABLE_PRODUCTS_TAGS_OPTIONS, $option, $action, "language_id = '".(int)$languages['languages_id']."' ".$where);
          }

          return $this->GetSingleOption($optionId);
      }

      /**
       * Insert a value by given option id
       *
       * @param int $optionId The option id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The value data
       */
      public function InsertValue(int $optionId, array $options): array
      {
          // Input validation
          if (empty($optionId)) {
              throw new Exception('Option ID required');
          }

          $values = $this->InsertUpdateValue(0, $options);
          
          return $values;
      }
      
      /**
       * Insert or Update a value by given optionid and value id.
       *
       * @param int $optionId The option id
       * @param int $valueId The value id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The value data
       */
      public function InsertUpdateValue(int $optionId, int $valueId, array $options): array
      {
          // Input validation
          if (empty($optionId)) {
              throw new Exception('Option ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $languages_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_LANGUAGES);
          while ($languages = xtc_db_fetch_array($languages_query)) {
              $where = '';
              if ($valueId > 0) {
                  $where = "AND values_id = '".(int)$valueId."'";
                  $values_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_PRODUCTS_TAGS_VALUES."
                                                 WHERE language_id = '".(int)$languages['languages_id']."'
                                                       ".$where);
                  if (xtc_db_num_rows($values_query) < 1) {
                      $action = 'insert';
                      $values = $this->getDefaultTableValues(TABLE_PRODUCTS_TAGS_VALUES);
                      $values['options_id'] = $optionId;
                      $values['values_id'] = $valueId;
                      $values['languages_id'] = (int)$languages['languages_id'];
                  } else {
                      $action = 'update';
                      $values = xtc_db_fetch_array($values_query);
                  }
              } else {
                  $action = 'insert';
                  $values = $this->getDefaultTableValues(TABLE_PRODUCTS_TAGS_VALUES);
                  
                  if ($valueId < 1) {
                      $next_id_query = xtc_db_query("SELECT max(values_id) as values_id 
                                                       FROM " . TABLE_PRODUCTS_TAGS_VALUES . "");
                      $next_id = xtc_db_fetch_array($next_id_query);
                      $valueId = $next_id['values_id'] + 1;
                  }

                  $values['options_id'] = $optionId;
                  $values['values_id'] = $valueId;
                  $values['languages_id'] = (int)$languages['languages_id'];
              }
    
              foreach ($values as $key => $value) {
                  if (isset($this->options[$languages['code']][$key])) {
                      $values[$key] = $this->options[$languages['code']][$key];
                  }
              }
    
              // Input validation
              $this->checkTableData(TABLE_PRODUCTS_TAGS_VALUES, $values);
              xtc_db_perform(TABLE_PRODUCTS_TAGS_VALUES, $values, $action, "language_id = '".(int)$languages['languages_id']."' ".$where);
          }

          return $this->GetSingleValue($valueId);
      }

  }
