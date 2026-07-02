<?php

/**
 * /includes/external/api/v1/Service/Country/GetSingleTaxRate.php
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
    path: '/api/v1/countries/tax_rates/{Id}',
    tags: ['Country'],
    description: 'Get tax rates data by given Id',
    operationId: 'GetSingleTaxRate',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'tax rates Id'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'tax rates data',
        ),
        new OA\Response(
            response: 403,
            description: 'tax rates not found'
        ),
        new OA\Response(
            response: 500,
            description: 'tax rates Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class GetSingleTaxRate extends BaseService
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

        $taxRateId = (int)$args['id'];

        $result = $this->countryAction->GetSingleTaxRate($taxRateId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
