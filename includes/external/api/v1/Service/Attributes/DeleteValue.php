<?php

/**
 * /includes/external/api/v1/Service/Attributes/DeleteValue.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Attributes;

use api\v1\Service\BaseService;
use api\v1\Action\Attributes\AttributesAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/v1/attributes/values/{Id}',
    tags: ['Attributes'],
    description: 'Delete attributes values by given Id',
    operationId: 'DeleteAttributesValue',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'values Id'
        )
    ],
    responses:[
        new OA\Response(
            response: 204,
            description: 'no data',
        ),
        new OA\Response(
            response: 403,
            description: 'values not found'
        ),
        new OA\Response(
            response: 500,
            description: 'values Id required'
        )
    ],
    security: [
        ['modified_auth' => ['DeleteValue']]
    ]
)]

final class DeleteValue extends BaseService
{
    /**
     * @var AttributesAction
     */
    private $attributesAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param AttributesAction $attributesAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(AttributesAction $attributesAction, Responder $responder)
    {
        $this->attributesAction = $attributesAction;
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

        $valueId = (int)$args['id'];

        $result = $this->attributesAction->DeleteValue($valueId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response)->withStatus(204);
    }
}
