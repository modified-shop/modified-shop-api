<?php

/**
 * /includes/external/api/v1/Service/Tags/DeleteOption.php
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

#[OA\Delete(
    path: '/api/v1/tags/options/{Id}',
    tags: ['Tags'],
    summary: 'Delete single tags option',
    description: 'Delete single tags option by given Id',
    operationId: 'DeleteTagsOption',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'option Id'
        )
    ],
    responses:[
        new OA\Response(
            response: 204,
            description: 'no data',
        ),
        new OA\Response(
            response: 403,
            description: 'option not found'
        ),
        new OA\Response(
            response: 500,
            description: 'option Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class DeleteOption extends BaseService
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

        $optionId = (int)$args['id'];

        $result = $this->tagsAction->DeleteOption($optionId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response)->withStatus(204);
    }
}
