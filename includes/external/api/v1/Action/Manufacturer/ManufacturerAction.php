<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Manufacturer;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;

  /**
   * Service.
   */
  final class ManufacturerAction extends BaseAction
  {
      use ManufacturerGetAction;
      use ManufacturerDeleteAction;

      /**
       * Insert a manufacturer by the given options.
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The manufacturer data
       */
      public function InsertManufacturer(array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if (isset($this->options[TABLE_MANUFACTURERS])) {
              $manufacturers = $this->InsertUpdateManufacturer(0, $this->options[TABLE_MANUFACTURERS]);
              $manufacturerId = $manufacturers['manufacturers_id'];
          }

          if (!isset($manufacturerId)) {
              throw new Exception('Manufacturer ID required');
          } else {
              if (isset($this->options[TABLE_MANUFACTURERS_INFO])) {
                  $manufacturers_description = $this->InsertUpdateDescription($manufacturerId, $this->options[TABLE_MANUFACTURERS_INFO]);
              }
          }

          return $this->GetManufacturerDetails($manufacturerId);
      }

      /**
       * Insert or Update a manufacturer by the given manufacturer id.
       *
       * @param int $manufacturerId The manufacturer id
       * @param mixed[] $options
       *
       * @return array The manufacturer data
       */
      public function InsertUpdateManufacturer(int $manufacturerId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if ($manufacturerId > 0) {
              $action = 'update';
              $manufacturers_query = xtc_db_query("SELECT *
                                                     FROM ".TABLE_MANUFACTURERS."
                                                    WHERE manufacturers_id = '".(int)$manufacturerId."'");
              if (xtc_db_num_rows($manufacturers_query) < 1) {
                  $this->errormessage(sprintf('Manufacturer not found: %s', $manufacturerId));
              } else {
                  $manufacturers = xtc_db_fetch_array($manufacturers_query);
                  $manufacturers['last_modified'] = 'now()';
              }
          } else {
              $action = 'insert';
              $manufacturers = $this->getDefaultTableValues(TABLE_MANUFACTURERS);
              $manufacturers['date_added'] = 'now()';
          }

          foreach ($manufacturers as $key => $value) {
              if (isset($this->options[$key])) {
                  $manufacturers[$key] = $this->options[$key];
              }
          }

          // Input validation
          $this->checkTableData(TABLE_MANUFACTURERS, $manufacturers);
          unset($manufacturers['manufacturers_id']);

          xtc_db_perform(TABLE_MANUFACTURERS, $manufacturers, $action, "manufacturers_id = '".(int)$manufacturerId."'");
          if ($action == 'insert') {
              $manufacturerId = xtc_db_insert_id();
          }

          return $this->getManufacturer($manufacturerId);
      }

      /**
       * Insert or Update a manufacturer by the given manufacturer id.
       *
       * @param int $manufacturerId The manufacturer id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The manufacturer data
       */
      public function InsertUpdateDescription(int $manufacturerId, array $options): array
      {
          // Input validation
          if (empty($manufacturerId)) {
              throw new Exception('Manufacturer ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $manufacturers_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_MANUFACTURERS."
                                                WHERE manufacturers_id = '".(int)$manufacturerId."'");
          if (xtc_db_num_rows($manufacturers_query) < 1) {
              $this->errormessage(sprintf('Manufacturer not found: %s', $manufacturerId));
          } else {
              $languages_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_LANGUAGES);
              while ($languages = xtc_db_fetch_array($languages_query)) {
                  $manufacturers_description_query = xtc_db_query("SELECT *
                                                                     FROM ".TABLE_MANUFACTURERS_INFO."
                                                                    WHERE manufacturers_id = '".(int)$manufacturerId."'
                                                                      AND languages_id = '".(int)$languages['languages_id']."'");
                  if (xtc_db_num_rows($manufacturers_description_query) > 0) {
                      $action = 'update';
                      $manufacturers_description = xtc_db_fetch_array($manufacturers_description_query);
                  } else {
                      $action = 'insert';
                      $manufacturers_description = $this->getDefaultTableValues(TABLE_MANUFACTURERS_INFO);
                      $manufacturers_description['manufacturers_id'] = (int)$manufacturerId;
                      $manufacturers_description['languages_id'] = (int)$languages['languages_id'];
                  }

                  foreach ($manufacturers_description as $key => $value) {
                      if (isset($this->options[$languages['code']][$key])) {
                          $manufacturers_description[$key] = $this->options[$languages['code']][$key];
                      }
                  }

                  // Input validation
                  $this->checkTableData(TABLE_MANUFACTURERS_INFO, $manufacturers_description);
                  xtc_db_perform(TABLE_MANUFACTURERS_INFO, $manufacturers_description, $action, "manufacturers_id = '".(int)$manufacturerId."' AND languages_id = '".(int)$languages['languages_id']."'");
              }
          }

          return $this->getManufacturerDescription($manufacturerId);
      }

      /**
       * Insert or Update a product by the given manufacturer id.
       *
       * @param int $manufacturerId The manufacturer id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The manufacturer data
       */
      public function InsertUpdateProducts(int $manufacturerId, array $options): array
      {
          // Input validation
          if (empty($manufacturerId)) {
              throw new Exception('Manufacturer ID required');
          }

          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          $manufacturers_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_MANUFACTURERS."
                                                WHERE manufacturers_id = '".(int)$manufacturerId."'");
          if (xtc_db_num_rows($manufacturers_query) < 1) {
              $this->errormessage(sprintf('Manufacturer not found: %s', $manufacturerId));
          } else {
              if (!isset($this->options['products_id'])) {
                  throw new Exception(sprintf('Product ID required'));
              } else {
                  xtc_db_query("UPDATE ".TABLE_PRODUCTS." 
                                   SET manufacturers_id = '".(int)$manufacturerId."'
                                 WHERE products_id = '".(int)$this->options['products_id']."'");
              }
          }

          return $this->GetManufacturerProducts($manufacturerId);
      }

      /**
       * Insert or update image of a manufacturer by the given manufacturer id.
       *
       * @param int $manufacturerId The manufacturer id
       *
       * @throws Exception
       *
       * @return array The manufacturer data
       */
      public function InsertUpdateImage(int $manufacturerId): array
      {
          // Input validation
          if (empty($manufacturerId)) {
              throw new Exception('Manufacturer ID required');
          }

          $manufacturer_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_MANUFACTURERS."
                                               WHERE manufacturers_id = '".(int)$manufacturerId."'");
          if (xtc_db_num_rows($manufacturer_query) < 1) {
              $this->errormessage(sprintf('Manufacturer not found: %s', $manufacturerId));
          } else {
              define('_VALID_XTC', true);

              // include needed classes
              require_once (DIR_FS_CATALOG.DIR_ADMIN.'includes/classes/'.IMAGE_MANIPULATOR);

              if ($manufacturers_image = xtc_try_upload('manufacturers_image', DIR_FS_CATALOG.DIR_WS_IMAGES.'manufacturers/original_images/', '777', $this->accepted_image_extensions, $this->accepted_image_mime_types)) {
                  $manufacturers_image_name = preg_replace('/[^\d\w\-\_\.]/', '', $manufacturers_image->filename);

                  rename(DIR_FS_CATALOG.DIR_WS_IMAGES.'manufacturers/original_images/'.$manufacturers_image->filename, DIR_FS_CATALOG.DIR_WS_IMAGES.'manufacturers/original_images/'.$manufacturers_image_name);

                  //image chmod
                  chmod(DIR_FS_CATALOG.DIR_WS_IMAGES.'manufacturers/original_images/'.$manufacturers_image_name, 0644);

                  xtc_db_query("UPDATE ".TABLE_MANUFACTURERS."
                                   SET manufacturers_image = '".xtc_db_input($manufacturers_image_name)."'
                                 WHERE manufacturers_id = '".(int)$manufacturerId."'");

                  $a = new \image_manipulation(DIR_FS_CATALOG.DIR_WS_IMAGES.'manufacturers/original_images/'.$manufacturers_image_name, MANUFACTURER_IMAGE_WIDTH, MANUFACTURER_IMAGE_HEIGHT, DIR_FS_CATALOG.DIR_WS_IMAGES.'manufacturers/'.$manufacturers_image_name, IMAGE_QUALITY, '');
                  $a->create();
              }
          }

          return $this->getManufacturer($manufacturerId);
      }

  }
