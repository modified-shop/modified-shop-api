<?php

/**
 * /includes/external/api/v1/Service/Customer/DeleteWishlist.php
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
use Exception;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/v1/customers/{Id}/wishlist/{bId}',
    tags: ['Customer'],
    summary: 'Delete single customer wishlist',
    description: 'Delete single customer wishlist by given Id',
    operationId: 'DeleteWishlist',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'customer Id'
        ),
        new OA\Parameter(
            name: 'bId',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'customer wishlist Id'
        )
    ],
    responses:[
        new OA\Response(
            response: 204,
            description: 'no data'
        ),
        new OA\Response(
            response: 403,
            description: 'customer not found or customer wishlist not found'
        ),
        new OA\Response(
            response: 500,
            description: 'customer Id required or customer wishlist Id required'
        ),
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class DeleteWishlist extends BaseService
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
        $customersBasketId = (int)$args['bid'];

        // Input validation
        if (empty($customersBasketId)) {
            throw new Exception('Customer basket ID required');
        }

        $result = $this->customerAction->DeleteWishlist($customerId, $customersBasketId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response)->withStatus(204);
    }
}
