<?php

/**
 * /includes/external/api/v1/Service/Category/GetCategories.php
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

#[OA\Get(
    path: '/api/v1/categories',
    tags: ['Category'],
    description: 'Get categories data',
    operationId: 'GetCategories',
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
            description: 'categories status'
        ),
        new OA\Parameter(
            name: 'parent',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'parent Id'
        ),
        new OA\Parameter(
            name: 'from',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'date added'
        ),
        new OA\Parameter(
            name: 'to',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'date added'
        ),
        new OA\Parameter(
            name: 'with',
            in: 'query',
            schema: new OA\Schema(
                type: 'string'
            ),
            description: 'included results (comma separated list). Possible values: products'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'categories data',
        ),
        new OA\Response(
            response: 403,
            description: 'no categories found'
        )
    ],
    security: [
        ['modified_auth' => ['GetCategories']]
    ]
)]

final class GetCategories extends BaseService
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

        $params = $request->getQueryParams();
        $params['path'] = $request->getUri()->getPath();

        $result = $this->categoryAction->GetCategories($params);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
