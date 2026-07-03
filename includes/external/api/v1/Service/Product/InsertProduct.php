<?php

/**
 * /includes/external/api/v1/Service/Product/InsertProduct.php
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
    path: '/api/v1/products',
    tags: ['Product'],
    description: 'Insert single order',
    operationId: 'InsertProduct',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'products',
                        type: 'object',
                        description: 'Columns of the products database table. The exact set is '
                            . 'installation-specific; discover it via GET /api/v1/schema/products.'
                    ),
                    new OA\Property(
                        property: 'products_description',
                        type: 'object',
                        description: 'Per-language product text, keyed by language code (e.g. "de", "en"). Each '
                            . 'value is an object of products_description columns. Discover the exact columns '
                            . 'via GET /api/v1/schema/products_description.'
                    ),
                    new OA\Property(
                        property: 'products_to_categories',
                        type: 'object',
                        description: 'Columns of the products_to_categories database table, to assign the new '
                            . 'product to a category. Discover the exact set via '
                            . 'GET /api/v1/schema/products_to_categories.'
                    )
                ]
            )
        )
    ),
    responses:[
        new OA\Response(
            response: 201,
            description: 'product data',
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertProduct extends BaseService
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

        $data = (array)$request->getParsedBody();

        $result = $this->productAction->InsertProduct($data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
