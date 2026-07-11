<?php

/**
 * /includes/external/api/v1/Service/Manufacturer/InsertUpdateManufacturer.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Manufacturer;

use api\v1\Service\BaseService;
use api\v1\Action\Manufacturer\ManufacturerAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/v1/manufacturers/{Id}/manufacturers',
    tags: ['Manufacturer'],
    summary: 'Update manufacturers base data (manufacturers database table only)',
    description: 'Update manufacturers base data (manufacturers database table only) by given Id',
    operationId: 'InsertUpdateManufacturer',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'manufacturer Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                description: 'Columns of the manufacturers database table, sent flat (unlike POST /manufacturers, '
                    . 'this endpoint does not nest fields under a "manufacturers" key). The exact set is '
                    . 'installation-specific; discover it via GET /api/v1/schema/manufacturers.'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'manufacturers data',
        ),
        new OA\Response(
            response: 403,
            description: 'manufacturer not found'
        ),
        new OA\Response(
            response: 500,
            description: 'manufacturer Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateManufacturer extends BaseService
{
    /**
     * @var ManufacturerAction
     */
    private $manufacturerAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param ManufacturerAction $manufacturerAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(ManufacturerAction $manufacturerAction, Responder $responder)
    {
        $this->manufacturerAction = $manufacturerAction;
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

        $manufacturerId = ((isset($args['id'])) ? (int)$args['id'] : 0);
        $data = (array)$request->getParsedBody();

        $result = $this->manufacturerAction->InsertUpdateManufacturer($manufacturerId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        if ($manufacturerId > 0) {
            return $this->responder->withJson($response, $result);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
