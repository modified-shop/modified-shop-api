<?php

/**
 * /includes/external/api/v1/Service/Order/InsertUpdateOrderProductDownload.php
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
    path: '/api/v1/orders/{Id}/products_download/{dId}',
    tags: ['Order'],
    description: 'Update order products download data by given Id',
    operationId: 'InsertUpdateOrderProductDownload',
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
            name: 'dId',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'order products download Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                description: 'Columns of the orders_products_download database table, sent flat. Discover the '
                    . 'exact set via GET /api/v1/schema/orders_products_download.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'order products data'
        ),
        new OA\Response(
            response: 403,
            description: 'order not found or order products download not found'
        ),
        new OA\Response(
            response: 500,
            description: 'order Id required or order products download Id required'
        ),
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateOrderProductDownload extends BaseService
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
        $orderProductsDownloadId = (int)$args['did'];
        $data = (array)$request->getParsedBody();

        $result = $this->orderAction->InsertUpdateOrderProductDownload($orderId, $orderProductsDownloadId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
