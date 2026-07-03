<?php

/**
 * /includes/external/api/v1/Service/Order/InsertUpdateOrderStatusHistory.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Order;

use api\v1\Service\BaseService;
use api\v1\Action\Order\OrderAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/v1/orders/{Id}/status_history/{hId}',
    tags: ['Order'],
    description: 'Update order status history data by given Id',
    operationId: 'InsertUpdateOrderStatusHistory',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'order Id'
        ),
        new OA\Parameter(
            name: 'hId',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'order status history Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                description: 'Columns of the orders_status_history database table, sent flat. Discover the '
                    . 'exact set via GET /api/v1/schema/orders_status_history.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'order status history data'
        ),
        new OA\Response(
            response: 403,
            description: 'order not found or order status history not found'
        ),
        new OA\Response(
            response: 500,
            description: 'order Id required or order status history Id required'
        ),
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateOrderStatusHistory extends BaseService
{
    /**
     * @var OrderAction
     */
    private $orderAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param OrderAction $orderAction The order reader
     * @param Responder $responder The responder
     */
    public function __construct(OrderAction $orderAction, Responder $responder)
    {
        $this->orderAction = $orderAction;
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

        $orderId = (int)$args['id'];
        $orderStatusHistoryId = (int)$args['hid'];
        $data = (array)$request->getParsedBody();

        $result = $this->orderAction->InsertUpdateOrderStatusHistory($orderId, $orderStatusHistoryId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
