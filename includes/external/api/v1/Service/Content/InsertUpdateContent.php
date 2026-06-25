<?php

/**
 * /includes/external/api/v1/Service/Content/InsertUpdateContent.php
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

#[OA\Put(
    path: '/api/v1/contents/{Id}',
    tags: ['Content'],
    description: 'Update content data by given Id',
    operationId: 'InsertUpdateContent',
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
    responses: [
        new OA\Response(
            response: 201,
            description: 'content data',
        ),
        new OA\Response(
            response: 403,
            description: 'content not found'
        ),
        new OA\Response(
            response: 500,
            description: 'content group Id required'
        )
    ],
    security: [
        ['modified_auth' => ['InsertUpdateContent']]
    ]
)]

final class InsertUpdateContent extends BaseService
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
     * @param ContentAction $contentAction The content reader
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

        $result = $this->contentAction->InsertUpdateContent($contentGroupId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
