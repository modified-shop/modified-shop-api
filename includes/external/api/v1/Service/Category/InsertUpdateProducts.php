<?php

/**
 * /includes/external/api/v1/Service/Category/InsertUpdateProducts.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Category;

use api\v1\Service\BaseService;
use api\v1\Action\Category\CategoryAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/categories/{Id}/products',
    tags: ['Category'],
    summary: 'Insert categories products',
    description: 'Insert categories products by given Id',
    operationId: 'InsertCategoriesProducts',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'category Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                required: ['products_id'],
                properties: [
                    new OA\Property(
                        property: 'products_id',
                        type: 'integer',
                        description: 'The product Id to assign to this category'
                    )
                ],
                description: 'Additional columns of the products_to_categories database table may be sent flat '
                    . 'alongside products_id. Discover the exact set via GET /api/v1/schema/products_to_categories.'
            )
        )
    ),
    responses:[
        new OA\Response(
            response: 201,
            description: 'categories data',
        ),
        new OA\Response(
            response: 403,
            description: 'category not found'
        ),
        new OA\Response(
            response: 500,
            description: 'category Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

#[OA\Put(
    path: '/api/v1/categories/{Id}/products',
    tags: ['Category'],
    summary: 'Update categories products',
    description: 'Update categories products by given Id',
    operationId: 'UpdateCategoriesProducts',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'category Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                required: ['products_id'],
                properties: [
                    new OA\Property(
                        property: 'products_id',
                        type: 'integer',
                        description: 'The product Id to assign to this category'
                    )
                ],
                description: 'Additional columns of the products_to_categories database table may be sent flat '
                    . 'alongside products_id. Discover the exact set via GET /api/v1/schema/products_to_categories.'
            )
        )
    ),
    responses:[
        new OA\Response(
            response: 201,
            description: 'categories data',
        ),
        new OA\Response(
            response: 403,
            description: 'category not found'
        ),
        new OA\Response(
            response: 500,
            description: 'category Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateProducts extends BaseService
{
    /**
     * @var CategoryAction
     */
    private $categoryAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param CategoryAction $categoryAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(CategoryAction $categoryAction, Responder $responder)
    {
        $this->categoryAction = $categoryAction;
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

        $categoryId = ((isset($args['id'])) ? (int)$args['id'] : 0);
        $data = (array)$request->getParsedBody();

        $result = $this->categoryAction->InsertUpdateProducts($categoryId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
