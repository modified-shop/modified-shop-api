<?php

/**
 * /includes/external/api/v1/Service/Dhl/GetDhl.php
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

#[OA\Get(
    path: '/api/v1/dhl/{Id}',
    tags: ['Dhl'],
    description: 'Create a DHL parcel label for the given order Id. All query parameters are optional and '
        . 'override the shop\'s DHL module configuration for this label only.',
    operationId: 'GetDhl',
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
            name: 'type',
            in: 'query',
            schema: new OA\Schema(type: 'integer'),
            description: 'DHL product; 0 = Paket, 1 = other (defaults from the shop\'s DHL module config)'
        ),
        new OA\Parameter(
            name: 'codeable',
            in: 'query',
            schema: new OA\Schema(type: 'boolean'),
            description: 'Cash on delivery (Nachnahme)'
        ),
        new OA\Parameter(
            name: 'insurance',
            in: 'query',
            schema: new OA\Schema(type: 'integer'),
            description: 'Insurance value flag (default 1)'
        ),
        new OA\Parameter(
            name: 'retoure',
            in: 'query',
            schema: new OA\Schema(type: 'boolean'),
            description: 'Include a return label'
        ),
        new OA\Parameter(
            name: 'avs',
            in: 'query',
            schema: new OA\Schema(type: 'string'),
            description: 'Address verification service setting (defaults from the shop\'s DHL module config)'
        ),
        new OA\Parameter(
            name: 'personal',
            in: 'query',
            schema: new OA\Schema(type: 'boolean'),
            description: 'Personal handover only (Eigenhändig)'
        ),
        new OA\Parameter(
            name: 'no_neighbour',
            in: 'query',
            schema: new OA\Schema(type: 'boolean'),
            description: 'Do not deliver to a neighbour'
        ),
        new OA\Parameter(
            name: 'ident',
            in: 'query',
            schema: new OA\Schema(type: 'string'),
            description: 'Identity check on delivery setting (defaults from the shop\'s DHL module config)'
        ),
        new OA\Parameter(
            name: 'dob',
            in: 'query',
            schema: new OA\Schema(type: 'string', format: 'date'),
            description: 'Date of birth for the identity check, format d.m.Y (defaults to the customer\'s date of birth)'
        ),
        new OA\Parameter(
            name: 'bulky',
            in: 'query',
            schema: new OA\Schema(type: 'boolean'),
            description: 'Bulky goods (Sperrgut)'
        ),
        new OA\Parameter(
            name: 'parcel_outlet',
            in: 'query',
            schema: new OA\Schema(type: 'boolean'),
            description: 'Allow delivery to a parcel shop (Packstation/Filiale)'
        ),
        new OA\Parameter(
            name: 'premium',
            in: 'query',
            schema: new OA\Schema(type: 'boolean'),
            description: 'DHL Premium International'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'DHL tracking data for the created label'
        ),
        new OA\Response(
            response: 400,
            description: 'DHL API rejected the request'
        ),
        new OA\Response(
            response: 403,
            description: 'order not found'
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

final class GetDhl extends BaseService
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

        $result = $this->dhlAction->GetDhl($orderId, $params);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
