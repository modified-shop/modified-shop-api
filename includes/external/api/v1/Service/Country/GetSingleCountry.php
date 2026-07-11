<?php

/**
 * /includes/external/api/v1/Service/Country/GetSingleCountry.php
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
    path: '/api/v1/countries/{Id}',
    tags: ['Country'],
    summary: 'Get countries data',
    description: 'Get countries data by given Id',
    operationId: 'GetSingleCountry',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'countries Id'
        ),
        new OA\Parameter(
            name: 'with',
            in: 'query',
            schema: new OA\Schema(
                type: 'string'
            ),
            description: 'included results (comma separated list). Possible values: zones'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'countries data',
        ),
        new OA\Response(
            response: 403,
            description: 'countries not found'
        ),
        new OA\Response(
            response: 500,
            description: 'countries Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class GetSingleCountry extends BaseService
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

        $countryId = (int)$args['id'];
        $params = $request->getQueryParams();
        $params['path'] = $request->getUri()->getPath();

        $result = $this->countryAction->GetSingleCountry($countryId, $params);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
