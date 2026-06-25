<?php

/**
 * /includes/external/api/v1/Service/Product/GetProducts.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Product;

use api\v1\Service\BaseService;
use api\v1\Action\Product\ProductAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/products',
    tags: ['Product'],
    description: 'Get products data',
    operationId: 'GetProducts',
    parameters: [
        new OA\Parameter(
            name: 'page',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Number of page'
        ),
        new OA\Parameter(
            name: 'limit',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Number of results per page'
        ),
        new OA\Parameter(
            name: 'status',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Status of product'
        ),
        new OA\Parameter(
            name: 'from',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Timestamp products_date_added'
        ),
        new OA\Parameter(
            name: 'to',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Timestamp products_date_added'
        ),
        new OA\Parameter(
            name: 'with',
            in: 'query',
            schema: new OA\Schema(
                type: 'string'
            ),
            description: 'included results (comma separated list). Possible values: categories, images, xsell, attributes, tags, content, offer, specials, reviews'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'product data',
        ),
        new OA\Response(
            response: 403,
            description: 'no product found'
        )
    ],
    security: [
        ['modified_auth' => ['GetProducts']]
    ]
)]

final class GetProducts extends BaseService
{
    /**
     * @var ProductAction
     */
    private $productAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param ProductAction $productAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(ProductAction $productAction, Responder $responder)
    {
        $this->productAction = $productAction;
        $this->responder = $responder;
    }

    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array<mixed> $args The route arguments
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $this->CheckAccess($request, $response);

        $params = $request->getQueryParams();
        $params['path'] = $request->getUri()->getPath();

        $result = $this->productAction->GetProducts($params);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
