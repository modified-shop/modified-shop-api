<?php

/**
 * /includes/external/api/v1/Service/Shipping/GetSingleCarrier.php
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

#[OA\Get(
    path: '/api/v1/shipping/carrier/{Id}',
    tags: ['Shipping'],
    description: 'Get single carriers data by given Id',
    operationId: 'GetSingleCarrier',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'carrier Id'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'carriers data',
        ),
        new OA\Response(
            response: 403,
            description: 'no carrier found'
        ),
        new OA\Response(
            response: 500,
            description: 'carrier Id required'
        )
    ],
    security: [
        ['modified_auth' => ['GetSingleCarrier']]
    ]
)]

final class GetSingleCarrier extends BaseService
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

        $carrierId = (int)$args['id'];

        $result = $this->shippingAction->GetSingleCarrier($carrierId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
