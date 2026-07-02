<?php

/**
 * /includes/external/api/v1/Service/Attributes/InsertUpdateOption.php
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

#[OA\Put(
    path: '/api/v1/attributes/options/{Id}',
    tags: ['Attributes'],
    description: 'Update single attribute options',
    operationId: 'InsertUpdateAttributesOption',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'options Id'
        )
    ],
    responses:[
        new OA\Response(
            response: 201,
            description: 'attributes options data',
        ),
        new OA\Response(
            response: 500,
            description: 'options Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateOption extends BaseService
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

        $optionId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $result = $this->attributesAction->InsertUpdateOption($optionId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
