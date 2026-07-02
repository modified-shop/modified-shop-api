<?php

/**
 * /includes/external/api/v1/Service/BaseService.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;
use Exception;

#[OA\Info(
    version: '1.0.0',
    title: 'modified eCommerce Shopsoftware API',
    description: ''
)]
#[OA\SecurityScheme(
    securityScheme: 'modified_auth',
    type: 'oauth2',
    flows: [
        new OA\Flow(
            flow: 'password',
            tokenUrl: '/api/v1/oauth',
            scopes: []
        )
    ]
)]

class BaseService
{
    /**
     * check API access.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @throws Exception
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
                                          FROM " . TABLE_CUSTOMERS . " c
                                          JOIN `api_access` aa
                                               ON aa.customers_id = c.customers_id
                                         WHERE c.customers_email_address = '" . xtc_db_input($token['sub']) . "'");
        $access = xtc_db_fetch_array($access_query);

        if (
            !isset($access[$className])
            || $access[$className] == 0
        ) {
            throw new Exception(sprintf('Access for %s required', $className));
        }
    }
}
