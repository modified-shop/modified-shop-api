<?php

/**
 * /includes/external/api/v1/Service/Product/InsertUpdateAttributes.php
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
    path: '/api/v1/products/{Id}/attributes',
    tags: ['Product'],
    description: 'Insert products attributes by given Id',
    operationId: 'UpdateProductsAttributes',
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
                required: ['options_id', 'options_values_id'],
                properties: [
                    new OA\Property(
                        property: 'options_id',
                        type: 'integer',
                        description: 'The attribute option Id (from /attributes/options)'
                    ),
                    new OA\Property(
                        property: 'options_values_id',
                        type: 'integer',
                        description: 'The attribute value Id (from /attributes/values), must be linked to '
                            . 'options_id'
                    ),
                    new OA\Property(
                        property: 'products_attributes_id',
                        type: 'integer',
                        description: 'An existing products_attributes row Id to update (omit to insert a new '
                            . 'combination)'
                    )
                ],
                description: 'Additional columns of the products_attributes database table may be sent flat. '
                    . 'Discover the exact set via GET /api/v1/schema/products_attributes.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'product attributes data',
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
    path: '/api/v1/products/{Id}/attributes',
    tags: ['Product'],
    description: 'Update products category by given Id',
    operationId: 'InsertProductsAttributes',
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
                required: ['options_id', 'options_values_id'],
                properties: [
                    new OA\Property(
                        property: 'options_id',
                        type: 'integer',
                        description: 'The attribute option Id (from /attributes/options)'
                    ),
                    new OA\Property(
                        property: 'options_values_id',
                        type: 'integer',
                        description: 'The attribute value Id (from /attributes/values), must be linked to '
                            . 'options_id'
                    ),
                    new OA\Property(
                        property: 'products_attributes_id',
                        type: 'integer',
                        description: 'An existing products_attributes row Id to update (omit to insert a new '
                            . 'combination)'
                    )
                ],
                description: 'Additional columns of the products_attributes database table may be sent flat. '
                    . 'Discover the exact set via GET /api/v1/schema/products_attributes.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'product attributes data',
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

final class InsertUpdateAttributes extends BaseService
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

        $productId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $result = $this->productAction->InsertUpdateAttributes($productId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
