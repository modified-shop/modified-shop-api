<?php

/**
 * /includes/external/api/v1/Service/Order/DeleteStatusHistory.php
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
use Exception;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/v1/orders/{Id}/status_history/{hId}',
    tags: ['Order'],
    description: 'Delete single order histroy by given Id',
    operationId: 'DeleteOrderStatusHistory',
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
            description: 'order histroy Id'
        )
    ],
    responses:[
        new OA\Response(
            response: 204,
            description: 'no data',
        ),
        new OA\Response(
            response: 403,
            description: 'order histroy not found'
        ),
        new OA\Response(
            response: 500,
            description: 'order Id required'
        ),
        new OA\Response(
            response: 500,
            description: 'order histroy Id required'
        )
    ],
    security: [
        ['modified_auth' => ['DeleteStatusHistory']]
    ]
)]

final class DeleteStatusHistory extends BaseService
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

        // Input validation
        if (empty($orderStatusHistoryId)) {
            throw new Exception('Order status history ID required');
        }

        $result = $this->orderAction->DeleteStatusHistory($orderId, $orderStatusHistoryId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response)->withStatus(204);
    }
}
