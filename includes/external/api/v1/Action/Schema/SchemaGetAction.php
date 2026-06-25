<?php

/**
 * /includes/external/api/v1/Action/Schema/SchemaGetAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Schema;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  trait SchemaGetAction
  {
      /**
       * Read a database schema by the given table.
       *
       * @param string $table The database table
       *
       * @throws Exception
       *
       * @return array The table data
       */
      public function GetSchema(string $table): array
      {
          // Input validation
          if (empty($table)) {
              throw new Exception('Table required');
          }
          
          $schema = [];
          $schema_query = xtc_db_query("SHOW TABLES LIKE '".xtc_db_input($table)."'");
          if (xtc_db_num_rows($schema_query) < 1) {
              return $this->errormessage(sprintf('Table not found: %s', $table));
          } else {
              $schema = $this->getDefaultTableInfo($table);
          }
          
          $result = $this->encode_request($schema);
          return $result;
      }

  }
