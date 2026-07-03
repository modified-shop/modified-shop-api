<?php

/**
 * /includes/external/api/v1/Service/Customer/InsertCustomer.php
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
    path: '/api/v1/customers',
    tags: ['Customer'],
    description: 'Insert single customer',
    operationId: 'InsertCustomer',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'customers',
                        type: 'object',
                        description: 'Columns of the customers database table. The exact set is '
                            . 'installation-specific; discover it via GET /api/v1/schema/customers.'
                    ),
                    new OA\Property(
                        property: 'customers_info',
                        type: 'object',
                        description: 'Columns of the customers_info database table. Discover the exact set via '
                            . 'GET /api/v1/schema/customers_info.'
                    ),
                    new OA\Property(
                        property: 'address_book',
                        type: 'object',
                        description: 'Columns of the address_book database table for the customer\'s primary '
                            . 'address. Discover the exact set via GET /api/v1/schema/address_book.'
                    )
                ]
            )
        )
    ),
    responses:[
        new OA\Response(
            response: 201,
            description: 'customer data',
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertCustomer extends BaseService
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

        $data = (array)$request->getParsedBody();

        $result = $this->customerAction->InsertCustomer($data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
