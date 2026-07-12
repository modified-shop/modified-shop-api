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
// Declared tags override the auto-generated ones (AugmentTags would otherwise
// set each description to the tag name); order here defines the section order
// in Swagger UI.
#[OA\Tag(name: 'Auth', description: 'Obtain, refresh and revoke JWT access tokens')]
#[OA\Tag(name: 'Attributes', description: 'Manage product options, option values and their assignments')]
#[OA\Tag(name: 'Campaign', description: 'Manage campaigns and their IP statistics')]
#[OA\Tag(name: 'Category', description: 'Manage categories, their descriptions, images and product assignments')]
#[OA\Tag(name: 'Configuration', description: 'Read shop configuration values and configuration groups')]
#[OA\Tag(name: 'Content', description: 'Manage content manager entries and their localized contents')]
#[OA\Tag(name: 'Country', description: 'Read countries, zones, geo zones, tax classes and tax rates')]
#[OA\Tag(name: 'Coupon', description: 'Manage coupons and their descriptions')]
#[OA\Tag(name: 'Currency', description: 'Manage currencies')]
#[OA\Tag(name: 'Customer', description: 'Manage customers, address books, baskets, wishlists, memos and status history')]
#[OA\Tag(name: 'Dhl', description: 'Create and delete DHL parcel labels for orders')]
#[OA\Tag(name: 'Language', description: 'Manage shop languages')]
#[OA\Tag(name: 'Manufacturer', description: 'Manage manufacturers, their descriptions, images and product assignments')]
#[OA\Tag(name: 'Newsletter', description: 'Manage newsletter recipients and their history')]
#[OA\Tag(name: 'Order', description: 'Manage orders, order products, totals, tracking and status history')]
#[OA\Tag(name: 'Product', description: 'Manage products, their descriptions, images, attributes, categories, specials, tags and cross-selling')]
#[OA\Tag(name: 'Schema', description: 'Inspect database table schemas')]
#[OA\Tag(name: 'Shipping', description: 'Manage shipping carriers and shipping status')]
#[OA\Tag(name: 'Tags', description: 'Manage tags options and values')]
#[OA\Tag(name: 'Webhook', description: 'Manage webhook subscriptions for event push notifications')]

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

        // The permission column is qualified as `{ResourceName}{ClassName}`
        $namespaceParts = explode('\\', $class->getNamespaceName());
        $resourceName = end($namespaceParts);

        $column = $resourceName . $className;

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
