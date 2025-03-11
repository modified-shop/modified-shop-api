<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service;

  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use Exception;

  /**
   * Service.
   */
  class BaseService
  {
      /**
       * check API access.
       *
       * @param ServerRequestInterface $request The request
       * @param ResponseInterface $response The response
       *
       * @return void
       */
      protected function CheckAccess(
          ServerRequestInterface $request,
          ResponseInterface $response
      ): void {
          $token = $request->getAttribute('token');
          
          $class = new \ReflectionClass(get_class($this));
          $className = $class->getShortName();
                   
          $access_query = xtc_db_query("SELECT aa.*
                                          FROM ".TABLE_CUSTOMERS." c
                                          JOIN `api_access` aa
                                               ON aa.customers_id = c.customers_id
                                         WHERE c.customers_email_address = '".xtc_db_input($token['usr'])."'");
          $access = xtc_db_fetch_array($access_query);
          
          if (!isset($access[$className])
              || $access[$className] == 0
              )
          {
              throw new Exception(sprintf('Access for %s required', $className));
          }
      }

  }