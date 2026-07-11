<?php

/**
 * /includes/external/api/v1/Service/Product/InsertUpdateImagesDescription.php
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
    path: '/api/v1/products/{Id}/images/{iId}/description',
    tags: ['Product'],
    summary: 'Insert products images description',
    description: 'Insert products images description by given Ids',
    operationId: 'UpdateProductsImagesDescription',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'product Id'
        ),
        new OA\Parameter(
            name: 'iId',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'image Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                description: 'Per-language image text, keyed by language code (e.g. "de", "en"). Each value is '
                    . 'an object of products_images_description columns. Discover the exact columns via '
                    . 'GET /api/v1/schema/products_images_description.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'product images data',
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
    path: '/api/v1/products/{Id}/images/{iId}/description',
    tags: ['Product'],
    summary: 'Update products images description',
    description: 'Update products images description by given Ids',
    operationId: 'InsertProductsImagesDescription',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'product Id'
        ),
        new OA\Parameter(
            name: 'iId',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'image Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                description: 'Per-language image text, keyed by language code (e.g. "de", "en"). Each value is '
                    . 'an object of products_images_description columns. Discover the exact columns via '
                    . 'GET /api/v1/schema/products_images_description.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'product images data',
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

final class InsertUpdateImagesDescription extends BaseService
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
        $imageId = (int)$args['iid'];
        $data = (array)$request->getParsedBody();

        $result = $this->productAction->InsertUpdateImagesDescription($productId, $imageId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
