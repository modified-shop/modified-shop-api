<?php

/**
 * /includes/external/api/v1/Service/Shipping/InsertCarrier.php
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
    path: '/api/v1/shipping/carrier',
    tags: ['Shipping'],
    summary: 'Insert single carrier',
    description: 'Insert single carrier',
    operationId: 'InsertCarrier',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                description: 'Columns of the carriers database table, sent flat (e.g. carrier_name, which must '
                    . 'be unique). The exact set is installation-specific; discover it via '
                    . 'GET /api/v1/schema/carriers.'
            )
        )
    ),
    responses:[
        new OA\Response(
            response: 201,
            description: 'carriers data',
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertCarrier extends BaseService
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

        $result = $this->shippingAction->InsertCarrier($data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
