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
  require_once(DIR_FS_INC.'xtc_try_upload.inc.php');

  // include needed classes
  require_once(DIR_WS_CLASSES.'upload.php');

  /**
   * Service.
   */
  class BaseAction
  {
      /**
       * @var mixed[]
       */
      protected $options = [
          "status" => null,
          "parent" => null,
          "from" => null,
          "to" => null,
          "with" => null,
          "page" => 1,
          "limit" => 10,
      ];

      /**
       * @var Excetion
       */
      protected $Excetion = true;

      /**
       * @var accepted_image_files
       */
      protected $accepted_image_files_extensions = array("jpg","jpeg","jpe","gif","png","bmp","tiff","tif","bmp");
      protected $accepted_image_files_mime_types = array("image/jpeg","image/gif","image/png","image/bmp");

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
       * encoding given array or string.
       *
       * @param mixed[] $data
       *
       * @return mixed
       */
      protected function encode_request($string)
      {
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
      protected function hydrate(array $data = []): void
      {
          foreach ($data as $key => $value) {
              $key = str_replace(".", " ", $key);
              $method = lcfirst(ucwords($key));
              $method = str_replace(" ", "", $method);
              if (method_exists($this, $method)) {
                  call_user_func([$this, $method], $value);
              } else {
                  $this->options[$key] = $value;
              }
          }
      }

      /**
       * get default table data
       *
       * @param string $table
       *
       * @return array
       */
      protected function getDefaultTableValues($table): array
      {
          $default_array = [];
          $default_query = xtc_db_query("SHOW COLUMNS FROM ".$table."");
          while ($default = xtc_db_fetch_array($default_query)) {      
              $value = '';
              if ($default['Default'] != '') {
                  $value = $default['Default'];
              } elseif (strtolower($default['Null']) == 'no'
                        && (strpos(strtolower($default['Type']), 'int') !== false
                            || strpos(strtolower($default['Type']), 'decimal') !== false
                            )
                        )
              {
                  $value = 0;
              }
              $default_array[$default['Field']] = $value;
          }
          
          return $default_array;
      }

      /**
       * get default table data
       *
       * @param string $table
       *
       * @return array
       */
      protected function getDefaultTableInfo($table): array
      {
          $default_array = [];
          $default_query = xtc_db_query("SHOW COLUMNS FROM ".$table."");
          while ($default = xtc_db_fetch_array($default_query)) {      
              $default_array[$default['Field']] = [
                'type' => ((strpos($default['Type'], '(') !== false) ? substr($default['Type'], 0, strpos($default['Type'], '(')) : $default['Type']),
                'length' => (int)preg_replace('/[^\d]/', '', $default['Type']),
              ];
          }
          
          return $default_array;
      }
  
      /**
       * get default table data
       *
       * @param string $table
       * @param array $data
       *
       * @throws Exception
       *
       * @return void
       */
      protected function checkTableData($table, array $data): void
      {
          $error = [];
          $default_array = $this->getDefaultTableInfo($table);
          foreach ($default_array as $key => $info) {
              if (strpos($info['type'], 'int') !== false
                  && is_numeric($data[$key]) === false
                  )
              {
                  $error[$key][] = sprintf('Not expected format: %s', $info['type']);
              } elseif (strpos($info['type'], 'int') === false
                  && $info['length'] > 0
                  && strlen($data[$key]) > $info['length']
                  )
              {
                  $error[$key][] = sprintf('Value length greater then allowed: %s', $info['length']);
              }
          }
          
          if (count($error) > 0) {
            throw new Exception(json_encode($error));
          }
      }

  }