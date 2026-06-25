<?php

/**
 * /includes/external/api/v1/Service/Product/DeleteTags.php
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
use Exception;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/v1/products/{Id}/tags/{tId}',
    tags: ['Product'],
    description: 'Delete single tag from a product by given Id',
    operationId: 'DeleteTagsProducts',
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
            name: 'tId',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'tag Id'
        )
    ],
    responses:[
        new OA\Response(
            response: 204,
            description: 'no data',
        ),
        new OA\Response(
            response: 403,
            description: 'product not found'
        ),
        new OA\Response(
            response: 403,
            description: 'tag not found'
        ),
        new OA\Response(
            response: 500,
            description: 'product Id required'
        ),
        new OA\Response(
            response: 500,
            description: 'tag Id required'
        )
    ],
    security: [
        ['modified_auth' => ['DeleteTags']]
    ]
)]

final class DeleteTags extends BaseService
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
        $tagsId = (int)$args['tid'];

        // Input validation
        if (empty($tagsId)) {
            throw new Exception('Tag ID required');
        }

        $result = $this->productAction->DeleteTags($productId, $tagsId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response)->withStatus(204);
    }
}
