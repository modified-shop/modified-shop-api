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

  /**
   * Service.
   */
  final class AttributesAction extends BaseAction
  {
      use AttributesGetAction;
      use AttributesDeleteAction;

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
                  $where = "AND products_options_id = '".(int)$optionId."'";
                  $option_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_PRODUCTS_OPTIONS."
                                                 WHERE language_id = '".(int)$languages['languages_id']."'
                                                       ".$where);
                  if (xtc_db_num_rows($option_query) < 1) {
                      $action = 'insert';
                      $option = $this->getDefaultTableValues(TABLE_PRODUCTS_OPTIONS);
                      $option['products_options_id'] = $optionId;
                      $option['language_id'] = (int)$languages['languages_id'];
                  } else {
                      $action = 'update';
                      $option = xtc_db_fetch_array($option_query);
                  }
              } else {
                  $action = 'insert';
                  $option = $this->getDefaultTableValues(TABLE_PRODUCTS_OPTIONS);
                  
                  if ($optionId < 1) {
                      $next_id_query = xtc_db_query("SELECT max(products_options_id) as products_options_id 
                                                       FROM " . TABLE_PRODUCTS_OPTIONS . "");
                      $next_id = xtc_db_fetch_array($next_id_query);
                      $optionId = $next_id['products_options_id'] + 1;
                  }

                  $option['products_options_id'] = $optionId;
                  $option['language_id'] = (int)$languages['languages_id'];
              }
    
              foreach ($option as $key => $value) {
                  if (isset($this->options[$languages['code']][$key])) {
                      $option[$key] = $this->options[$languages['code']][$key];
                  }
              }
    
              // Input validation
              $this->checkTableData(TABLE_PRODUCTS_OPTIONS, $option);
              xtc_db_perform(TABLE_PRODUCTS_OPTIONS, $option, $action, "language_id = '".(int)$languages['languages_id']."' ".$where);
          }

          return $this->GetSingleOption($optionId);
      }

      /**
       * Insert a value
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The value data
       */
      public function InsertValue(array $options): array
      {
          $values = $this->InsertUpdateValue(0, $options);
          
          return $values;
      }
      
      /**
       * Insert or Update a value by given value id.
       *
       * @param int $valueId The value id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The value data
       */
      public function InsertUpdateValue(int $valueId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $languages_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_LANGUAGES);
          while ($languages = xtc_db_fetch_array($languages_query)) {
              $where = '';
              if ($valueId > 0) {
                  $where = "AND products_options_values_id = '".(int)$valueId."'";
                  $values_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_PRODUCTS_OPTIONS_VALUES."
                                                 WHERE language_id = '".(int)$languages['languages_id']."'
                                                       ".$where);
                  if (xtc_db_num_rows($values_query) < 1) {
                      $values = 'insert';
                      $values = $this->getDefaultTableValues(TABLE_PRODUCTS_OPTIONS_VALUES);
                      $values['products_options_values_id'] = $valueId;
                      $values['language_id'] = (int)$languages['languages_id'];
                  } else {
                      $values = 'update';
                      $values = xtc_db_fetch_array($values_query);
                  }
              } else {
                  $action = 'insert';
                  $values = $this->getDefaultTableValues(TABLE_PRODUCTS_OPTIONS_VALUES);
                  
                  if ($valueId < 1) {
                      $next_id_query = xtc_db_query("SELECT max(products_options_values_id) as products_options_values_id 
                                                       FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "");
                      $next_id = xtc_db_fetch_array($next_id_query);
                      $valueId = $next_id['products_options_values_id'] + 1;
                  }

                  $values['products_options_values_id'] = $valueId;
                  $values['language_id'] = (int)$languages['languages_id'];
              }
    
              foreach ($values as $key => $value) {
                  if (isset($this->options[$languages['code']][$key])) {
                      $values[$key] = $this->options[$languages['code']][$key];
                  }
              }
    
              // Input validation
              $this->checkTableData(TABLE_PRODUCTS_OPTIONS_VALUES, $values);
              xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES, $values, $action, "language_id = '".(int)$languages['languages_id']."' ".$where);
          }

          return $this->GetSingleOption($valueId);
      }

  }
