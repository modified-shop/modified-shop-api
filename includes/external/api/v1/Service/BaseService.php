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

// Note: the version below is overridden at runtime from settings['version']
// (see config/routes.php); the literal only serves as a static fallback.
#[OA\Info(
    version: '1.0.0',
    title: 'modified eCommerce Shopsoftware API',
    description: 'REST API for the modified eCommerce Shopsoftware, giving external applications '
        . 'programmatic access to shop data (customers, products, categories, orders and more). '
        . 'Access is granted per customer account in the shop backend (Customers -> API Access). '
        . 'Use the Authorize button to log in with your API credentials (username and password); '
        . 'the returned JWT is valid for 10 minutes and is sent as a Bearer token on all protected endpoints.'
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

        // The permission column is qualified as `{group_id}_{className}`
        $namespaceParts = explode('\\', $class->getNamespaceName());
        $resourceName = end($namespaceParts);

        $group_query = xtc_db_query("SELECT group_id
                                         FROM `api_access_groups`
                                        WHERE resource_name = '" . xtc_db_input($resourceName) . "'");
        $group = xtc_db_fetch_array($group_query);

        $column = isset($group['group_id']) ? $group['group_id'] . '_' . $className : $className;

        $access_query = xtc_db_query("SELECT aa.*
                                          FROM " . TABLE_CUSTOMERS . " c
                                          JOIN `api_access` aa
                                               ON aa.customers_id = c.customers_id
                                         WHERE c.customers_email_address = '" . xtc_db_input($token['sub']) . "'");
        $access = xtc_db_fetch_array($access_query);

        if (
            !isset($access[$column])
            || $access[$column] == 0
        ) {
            throw new Exception(sprintf('Access for %s required', $className));
        }
    }
}
