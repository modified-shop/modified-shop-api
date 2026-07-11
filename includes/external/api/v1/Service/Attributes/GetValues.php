<?php

/**
 * /includes/external/api/v1/Service/Attributes/GetValues.php
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
    path: '/api/v1/attributes/values',
    tags: ['Attributes'],
    summary: 'Get attributes values',
    description: 'Get attributes values',
    operationId: 'GetAttributesValues',
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
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class GetValues extends BaseService
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

        $params = $request->getQueryParams();
        $params['path'] = $request->getUri()->getPath();

        $result = $this->attributesAction->GetValues($params);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
