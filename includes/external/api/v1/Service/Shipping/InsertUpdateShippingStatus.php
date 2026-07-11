<?php

/**
 * /includes/external/api/v1/Service/Shipping/InsertUpdateShippingStatus.php
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

#[OA\Put(
    path: '/api/v1/shipping/status/{Id}',
    tags: ['Shipping'],
    summary: 'Update shipping status data',
    description: 'Update shipping status data by given Id',
    operationId: 'InsertUpdateShippingStatus',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'shipping status Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                description: 'Per-language shipping status text, keyed by language code (e.g. "de", "en"). Each '
                    . 'value is an object of shipping_status columns. Discover the exact columns via '
                    . 'GET /api/v1/schema/shipping_status.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'shipping status data',
        ),
        new OA\Response(
            response: 403,
            description: 'shipping status not found'
        ),
        new OA\Response(
            response: 500,
            description: 'shipping status Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateShippingStatus extends BaseService
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

        $shippingStatusId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $result = $this->shippingAction->InsertUpdateShippingStatus($shippingStatusId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
