<?php

/**
 * /includes/external/api/v1/Service/Product/InsertUpdateImages.php
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
    path: '/api/v1/products/{Id}/images',
    tags: ['Product'],
    description: 'Insert products images by given Id',
    operationId: 'UpdateProductsImages',
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
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'image_id',
                        type: 'integer',
                        description: 'An existing products_images row Id to update (omit to insert a new image '
                            . 'row)'
                    ),
                    new OA\Property(
                        property: 'image_name',
                        type: 'string',
                        format: 'binary',
                        description: 'The image file to upload for this row'
                    )
                ],
                description: 'Additional columns of the products_images database table may be sent flat '
                    . 'alongside image_id. Per-language description fields (keyed by language code, e.g. "de", '
                    . '"en", each an object of products_images_description columns) may also be included, using '
                    . 'the same shape as PUT /products/{Id}/images/{iId}/description. Discover the exact column '
                    . 'sets via GET /api/v1/schema/products_images and GET /api/v1/schema/products_images_description.'
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
    path: '/api/v1/products/{Id}/images',
    tags: ['Product'],
    description: 'Update products images by given Id',
    operationId: 'InsertProductsImages',
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
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'image_id',
                        type: 'integer',
                        description: 'An existing products_images row Id to update (omit to insert a new image '
                            . 'row)'
                    ),
                    new OA\Property(
                        property: 'image_name',
                        type: 'string',
                        format: 'binary',
                        description: 'The image file to upload for this row'
                    )
                ],
                description: 'Additional columns of the products_images database table may be sent flat '
                    . 'alongside image_id. Per-language description fields (keyed by language code, e.g. "de", '
                    . '"en", each an object of products_images_description columns) may also be included, using '
                    . 'the same shape as PUT /products/{Id}/images/{iId}/description. Discover the exact column '
                    . 'sets via GET /api/v1/schema/products_images and GET /api/v1/schema/products_images_description.'
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

final class InsertUpdateImages extends BaseService
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

        $result = $this->productAction->InsertUpdateImages($productId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
