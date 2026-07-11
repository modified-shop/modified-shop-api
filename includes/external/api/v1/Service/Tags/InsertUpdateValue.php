<?php

/**
 * /includes/external/api/v1/Service/Tags/InsertUpdateValue.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Tags;

use api\v1\Service\BaseService;
use api\v1\Action\Tags\TagsAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/v1/tags/values/{Id}/{vId}',
    tags: ['Tags'],
    summary: 'Update tags values data',
    description: 'Update tags values data by given Ids',
    operationId: 'InsertUpdateTagsValue',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'option Id'
        ),
        new OA\Parameter(
            name: 'vId',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'value Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                description: 'Per-language value text, keyed by language code (e.g. "de", "en"). Each value is '
                    . 'an object of products_tags_values columns. Discover the exact columns via '
                    . 'GET /api/v1/schema/products_tags_values.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'values data'
        ),
        new OA\Response(
            response: 403,
            description: 'option not found or value not found'
        ),
        new OA\Response(
            response: 500,
            description: 'option Id required or value Id required'
        ),
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateValue extends BaseService
{
    /**
     * @var TagsAction
     */
    private $tagsAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param TagsAction $tagsAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(TagsAction $tagsAction, Responder $responder)
    {
        $this->tagsAction = $tagsAction;
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

        $optionId = (int)$args['oid'];
        $valueId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $result = $this->tagsAction->InsertUpdateValue($optionId, $valueId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
