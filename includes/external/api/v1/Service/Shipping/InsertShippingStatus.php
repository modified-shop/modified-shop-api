<?php

/**
 * /includes/external/api/v1/Service/Shipping/InsertShippingStatus.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Shipping;

use api\v1\Service\BaseService;
use api\v1\Action\Shipping\ShippingAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/shipping/status',
    tags: ['Shipping'],
    description: 'Insert single shipping status',
    operationId: 'InsertShippingStatus',
    responses:[
        new OA\Response(
            response: 201,
            description: 'shipping status data',
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertShippingStatus extends BaseService
{
    /**
     * @var ShippingAction
     */
    private $shippingAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param ShippingAction $shippingAction The shipping reader
     * @param Responder $responder The responder
     */
    public function __construct(ShippingAction $shippingAction, Responder $responder)
    {
        $this->shippingAction = $shippingAction;
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

        $result = $this->shippingAction->InsertShippingStatus($data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
