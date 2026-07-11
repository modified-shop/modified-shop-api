<?php

/**
 * /includes/external/api/v1/Service/Configuration/GetConfigurationGroups.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Configuration;

use api\v1\Service\BaseService;
use api\v1\Action\Configuration\ConfigurationAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/configurations/groups',
    tags: ['Configuration'],
    summary: 'Get configuration group data',
    description: 'Get configuration group data',
    operationId: 'GetConfigurationGroups',
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
            description: 'included results (comma separated list). Possible values: configuration'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'configuration data',
        ),
        new OA\Response(
            response: 403,
            description: 'no configuration group found'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class GetConfigurationGroups extends BaseService
{
    /**
     * @var ConfigurationAction
     */
    private $configurationAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param ConfigurationAction $configurationAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(ConfigurationAction $configurationAction, Responder $responder)
    {
        $this->configurationAction = $configurationAction;
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

        $result = $this->configurationAction->GetConfigurationGroups($params);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
