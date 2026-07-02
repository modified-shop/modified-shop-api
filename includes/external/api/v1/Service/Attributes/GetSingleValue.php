<?php

/**
 * /includes/external/api/v1/Service/Attributes/GetSingleValue.php
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

#[OA\Get(
    path: '/api/v1/attributes/values/{Id}',
    tags: ['Attributes'],
    description: 'Get single attributes values by given Id',
    operationId: 'GetSingleAttributesValue',
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
    responses: [
        new OA\Response(
            response: 200,
            description: 'attributes values data',
        ),
        new OA\Response(
            response: 403,
            description: 'no values found'
        ),
        new OA\Response(
            response: 500,
            description: 'values Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class GetSingleValue extends BaseService
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

        $result = $this->attributesAction->GetSingleValue($valueId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
