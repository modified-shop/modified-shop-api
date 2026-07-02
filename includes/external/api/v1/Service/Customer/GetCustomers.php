<?php

/**
 * /includes/external/api/v1/Service/Customer/GetCustomers.php
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

#[OA\Get(
    path: '/api/v1/customers',
    tags: ['Customer'],
    description: 'Get customers data',
    operationId: 'GetCustomers',
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
        ),
        new OA\Parameter(
            name: 'status',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Status of customer'
        ),
        new OA\Parameter(
            name: 'from',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Timestamp date_added'
        ),
        new OA\Parameter(
            name: 'to',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Timestamp date_added'
        ),
        new OA\Parameter(
            name: 'with',
            in: 'query',
            schema: new OA\Schema(
                type: 'string'
            ),
            description: 'included results (comma separated list). Possible values: info, ip, memo, history, address, basket, wishlist'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'customer data',
        ),
        new OA\Response(
            response: 403,
            description: 'no customer found'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class GetCustomers extends BaseService
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

        $params = $request->getQueryParams();
        $params['path'] = $request->getUri()->getPath();

        $result = $this->customerAction->GetCustomers($params);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
