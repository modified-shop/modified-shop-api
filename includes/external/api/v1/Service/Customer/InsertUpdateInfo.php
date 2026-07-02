<?php

/**
 * /includes/external/api/v1/Service/Customer/InsertUpdateInfo.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Customer;

use api\v1\Service\BaseService;
use api\v1\Action\Customer\CustomerAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/customers/{Id}/info',
    tags: ['Customer'],
    description: 'Insert customer info by given Id',
    operationId: 'InsertCustomerInfo',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'customer Id'
        )
    ],
    responses:[
        new OA\Response(
            response: 201,
            description: 'customer info data',
        ),
        new OA\Response(
            response: 403,
            description: 'no customer info found'
        ),
        new OA\Response(
            response: 500,
            description: 'customer Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

#[OA\Put(
    path: '/api/v1/customers/{Id}/info',
    tags: ['Customer'],
    description: 'Update customer info by given Id',
    operationId: 'UpdateCustomerInfo',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'customer Id'
        )
    ],
    responses:[
        new OA\Response(
            response: 201,
            description: 'customer info data',
        ),
        new OA\Response(
            response: 403,
            description: 'no customer info found'
        ),
        new OA\Response(
            response: 500,
            description: 'customer Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateInfo extends BaseService
{
    /**
     * @var CustomerAction
     */
    private $customerAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param CustomerAction $customerAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(CustomerAction $customerAction, Responder $responder)
    {
        $this->customerAction = $customerAction;
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

        $customerId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $result = $this->customerAction->InsertUpdateInfo($customerId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
