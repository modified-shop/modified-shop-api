<?php

/**
 * /includes/external/api/v1/Service/Country/GetGeoZones.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Country;

use api\v1\Service\BaseService;
use api\v1\Action\Country\CountryAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/countries/geo_zones',
    tags: ['Country'],
    description: 'Get geo zones data',
    operationId: 'GetGeoZones',
    parameters: [
        new OA\Parameter(
            name: 'page',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Number of page'
        ),
        new OA\Parameter(
            name: 'limit',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Number of results per page'
        ),
        new OA\Parameter(
            name: 'with',
            in: 'query',
            schema: new OA\Schema(
                type: 'string'
            ),
            description: 'included results (comma separated list). Possible values: countries'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'geo zones data',
        ),
        new OA\Response(
            response: 403,
            description: 'geo zones not found'
        )
    ],
    security: [
        ['modified_auth' => ['GetGeoZones']]
    ]
)]

final class GetGeoZones extends BaseService
{
    /**
     * @var CountryAction
     */
    private $countryAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param CountryAction $countryAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(CountryAction $countryAction, Responder $responder)
    {
        $this->countryAction = $countryAction;
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

        $params = $request->getQueryParams();
        $params['path'] = $request->getUri()->getPath();

        $result = $this->countryAction->GetGeoZones($params);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
