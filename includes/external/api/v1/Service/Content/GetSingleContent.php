<?php

/**
 * /includes/external/api/v1/Service/Content/GetSingleContent.php
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

#[OA\Get(
    path: '/api/v1/contents/{Id}',
    tags: ['Content'],
    summary: 'Get single content data',
    description: 'Get single content data by given Id',
    operationId: 'GetSingleContent',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'content group Id'
        ),
        new OA\Parameter(
            name: 'with',
            in: 'query',
            schema: new OA\Schema(
                type: 'string'
            ),
            description: 'included results (comma separated list). Possible values: content'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'contents data',
        ),
        new OA\Response(
            response: 403,
            description: 'no contents found'
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

final class GetSingleContent extends BaseService
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
        $params = $request->getQueryParams();
        $params['path'] = $request->getUri()->getPath();

        $result = $this->contentAction->GetSingleContent($contentGroupId, $params);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
