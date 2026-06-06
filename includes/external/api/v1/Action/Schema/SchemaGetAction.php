<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

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
              $this->errormessage(sprintf('Table not found: %s', $table));
          } else {
              $schema = $this->getDefaultTableInfo($table);
          }
          
          $result = $this->encode_request($schema);
          return $result;
      }

  }
