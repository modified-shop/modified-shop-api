<?php

/**
 * /includes/external/api/v1/Service/Dhl/DeleteDhl.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Dhl;

use api\v1\Service\BaseService;
use api\v1\Action\Dhl\DhlAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/v1/dhl/{Id}',
    tags: ['Dhl'],
    description: 'Delete a DHL parcel label for the given order Id. Exactly one of tracking_id or parcel_id '
        . 'must be given to identify which label to delete.',
    operationId: 'DeleteDhl',
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
            name: 'tracking_id',
            in: 'query',
            schema: new OA\Schema(type: 'integer'),
            description: 'The orders_tracking row Id to delete (alternative to parcel_id)'
        ),
        new OA\Parameter(
            name: 'parcel_id',
            in: 'query',
            schema: new OA\Schema(type: 'string'),
            description: 'The DHL parcel Id to delete (alternative to tracking_id)'
        )
    ],
    responses: [
        new OA\Response(
            response: 204,
            description: 'DHL label deleted'
        ),
        new OA\Response(
            response: 400,
            description: 'DHL API rejected the request'
        ),
        new OA\Response(
            response: 403,
            description: 'order not found, or no matching tracking record'
        ),
        new OA\Response(
            response: 500,
            description: 'order Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class DeleteDhl extends BaseService
{
    /**
     * @var DhlAction
     */
    private $dhlAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param DhlAction $dhlAction The order reader
     * @param Responder $responder The responder
     */
    public function __construct(DhlAction $dhlAction, Responder $responder)
    {
        $this->dhlAction = $dhlAction;
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
        $params = $request->getQueryParams();

        $result = $this->dhlAction->DeleteDhl($orderId, $params);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response)->withStatus(204);
    }
}
