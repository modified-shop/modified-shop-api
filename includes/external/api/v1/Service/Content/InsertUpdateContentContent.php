<?php

/**
 * /includes/external/api/v1/Service/Content/InsertUpdateContentContent.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Content;

use api\v1\Service\BaseService;
use api\v1\Action\Content\ContentAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/contents/{Id}/content',
    tags: ['Content'],
    summary: 'Insert content for a content data',
    description: 'Insert content for a content data by given Id',
    operationId: 'InsertContentContent',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'content group Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                type: 'object',
                description: 'Per-language content body, keyed by language code (e.g. "de", "en"). Each value is '
                    . 'an object of content_manager_content columns; include content_id in it to update an '
                    . 'existing entry (omit to insert a new one). Discover the exact columns via '
                    . 'GET /api/v1/schema/content_manager_content. A file may additionally be attached per '
                    . 'language under that language\'s content_file field to set content_manager_content.content_file.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'contents data',
        ),
        new OA\Response(
            response: 403,
            description: 'contents not found'
        ),
        new OA\Response(
            response: 500,
            description: 'content group Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

#[OA\Put(
    path: '/api/v1/contents/{Id}/content',
    tags: ['Content'],
    summary: 'Update content for a content data',
    description: 'Update content for a content data by given Id',
    operationId: 'UpdateContentContent',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'content group Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                type: 'object',
                description: 'Per-language content body, keyed by language code (e.g. "de", "en"). Each value is '
                    . 'an object of content_manager_content columns; include content_id in it to update an '
                    . 'existing entry (omit to insert a new one). Discover the exact columns via '
                    . 'GET /api/v1/schema/content_manager_content. A file may additionally be attached per '
                    . 'language under that language\'s content_file field to set content_manager_content.content_file.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'contents data',
        ),
        new OA\Response(
            response: 403,
            description: 'contents not found'
        ),
        new OA\Response(
            response: 500,
            description: 'content group Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateContentContent extends BaseService
{
    /**
     * @var ContentAction
     */
    private $contentAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param ContentAction $contentAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(ContentAction $contentAction, Responder $responder)
    {
        $this->contentAction = $contentAction;
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

        $contentGroupId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $result = $this->contentAction->InsertUpdateContentContent($contentGroupId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
