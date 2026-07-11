<?php

/**
 * /includes/external/api/v1/Service/Product/InsertUpdateXsell.php
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
    path: '/api/v1/products/{Id}/xsell',
    tags: ['Product'],
    summary: 'Insert products xsell',
    description: 'Insert products xsell by given Id',
    operationId: 'UpdateProductsXsell',
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
                required: ['xsell_id'],
                properties: [
                    new OA\Property(
                        property: 'xsell_id',
                        type: 'integer',
                        description: 'The Id of the related (cross-sell) product'
                    )
                ],
                description: 'Additional columns of the products_xsell database table may be sent flat '
                    . 'alongside xsell_id. Discover the exact set via GET /api/v1/schema/products_xsell.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'product xsell data',
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
    path: '/api/v1/products/{Id}/xsell',
    tags: ['Product'],
    summary: 'Update products xsell',
    description: 'Update products xsell by given Id',
    operationId: 'InsertProductsXsell',
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
                required: ['xsell_id'],
                properties: [
                    new OA\Property(
                        property: 'xsell_id',
                        type: 'integer',
                        description: 'The Id of the related (cross-sell) product'
                    )
                ],
                description: 'Additional columns of the products_xsell database table may be sent flat '
                    . 'alongside xsell_id. Discover the exact set via GET /api/v1/schema/products_xsell.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'product xsell data',
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

final class InsertUpdateXsell extends BaseService
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

        $result = $this->productAction->InsertUpdateXsell($productId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
