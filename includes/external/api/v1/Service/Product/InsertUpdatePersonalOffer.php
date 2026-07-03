<?php

/**
 * /includes/external/api/v1/Service/Product/InsertUpdatePersonalOffer.php
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
    path: '/api/v1/products/{Id}/offer',
    tags: ['Product'],
    description: 'Insert products offer by given Id',
    operationId: 'UpdateProductsPersonalOffer',
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
                required: ['status_id'],
                properties: [
                    new OA\Property(
                        property: 'status_id',
                        type: 'integer',
                        description: 'Selects which personal-offer table this row is stored in (the shop '
                            . 'maintains one such table per status_id); required on every request.'
                    ),
                    new OA\Property(
                        property: 'price_id',
                        type: 'integer',
                        description: 'An existing row Id (in the table selected by status_id) to update (omit '
                            . 'to insert a new one)'
                    )
                ],
                description: 'Additional columns may be sent flat alongside status_id and price_id. The target '
                    . 'table depends on status_id, so its exact column set cannot be discovered via a single '
                    . 'fixed /api/v1/schema/{table} call ahead of time.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'product offer data',
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
    path: '/api/v1/products/{Id}/offer',
    tags: ['Product'],
    description: 'Update products offer by given Id',
    operationId: 'InsertProductsPersonalOffer',
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
                required: ['status_id'],
                properties: [
                    new OA\Property(
                        property: 'status_id',
                        type: 'integer',
                        description: 'Selects which personal-offer table this row is stored in (the shop '
                            . 'maintains one such table per status_id); required on every request.'
                    ),
                    new OA\Property(
                        property: 'price_id',
                        type: 'integer',
                        description: 'An existing row Id (in the table selected by status_id) to update (omit '
                            . 'to insert a new one)'
                    )
                ],
                description: 'Additional columns may be sent flat alongside status_id and price_id. The target '
                    . 'table depends on status_id, so its exact column set cannot be discovered via a single '
                    . 'fixed /api/v1/schema/{table} call ahead of time.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'product offer data',
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

final class InsertUpdatePersonalOffer extends BaseService
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

        $result = $this->productAction->InsertUpdatePersonalOffer($productId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
