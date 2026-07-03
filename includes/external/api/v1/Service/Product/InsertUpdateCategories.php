<?php

/**
 * /includes/external/api/v1/Service/Product/InsertUpdateCategories.php
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

#[OA\Post(
    path: '/api/v1/products/{Id}/categories',
    tags: ['Product'],
    description: 'Insert products category by given Id',
    operationId: 'UpdateProductsCategory',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'product Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                required: ['categories_id'],
                properties: [
                    new OA\Property(
                        property: 'categories_id',
                        type: 'integer',
                        description: 'The category Id to assign the product to'
                    )
                ],
                description: 'Additional columns of the products_to_categories database table may be sent flat '
                    . 'alongside categories_id. Discover the exact set via '
                    . 'GET /api/v1/schema/products_to_categories.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'product category data',
        ),
        new OA\Response(
            response: 403,
            description: 'product not found'
        ),
        new OA\Response(
            response: 500,
            description: 'product Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

#[OA\Put(
    path: '/api/v1/products/{Id}/categories',
    tags: ['Product'],
    description: 'Update products category by given Id',
    operationId: 'InsertProductsCategory',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'product Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                required: ['categories_id'],
                properties: [
                    new OA\Property(
                        property: 'categories_id',
                        type: 'integer',
                        description: 'The category Id to assign the product to'
                    )
                ],
                description: 'Additional columns of the products_to_categories database table may be sent flat '
                    . 'alongside categories_id. Discover the exact set via '
                    . 'GET /api/v1/schema/products_to_categories.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'product category data',
        ),
        new OA\Response(
            response: 403,
            description: 'product not found'
        ),
        new OA\Response(
            response: 500,
            description: 'product Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateCategories extends BaseService
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

        $productId = ((isset($args['id'])) ? (int)$args['id'] : 0);
        $data = (array)$request->getParsedBody();

        $result = $this->productAction->InsertUpdateCategories($productId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        if ($productId > 0) {
            return $this->responder->withJson($response, $result);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
