<?php

/**
 * /includes/external/api/v1/Service/Order/InsertOrderStatus.php
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

#[OA\Post(
    path: '/api/v1/orders/status',
    tags: ['Order'],
    summary: 'Insert single order status',
    description: 'Insert single order status',
    operationId: 'InsertOrderStatus',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                description: 'Per-language order status text, keyed by language code (e.g. "de", "en"). Each '
                    . 'value is an object of orders_status columns. Discover the exact columns via '
                    . 'GET /api/v1/schema/orders_status.'
            )
        )
    ),
    responses:[
        new OA\Response(
            response: 201,
            description: 'order status data',
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertOrderStatus extends BaseService
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

        $data = (array)$request->getParsedBody();

        $result = $this->orderAction->InsertOrderStatus($data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
