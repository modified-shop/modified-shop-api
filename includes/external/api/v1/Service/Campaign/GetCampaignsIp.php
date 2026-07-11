<?php

/**
 * /includes/external/api/v1/Service/Campaign/GetCampaignsIp.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Campaign;

use api\v1\Service\BaseService;
use api\v1\Action\Campaign\CampaignAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/campaigns/{Id}/ip',
    tags: ['Campaign'],
    summary: 'Get campaigns ip data',
    description: 'Get campaigns ip data by given Id',
    operationId: 'GetCampaignsIp',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'campaigns Id'
        ),
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
            name: 'from',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Timestamp time'
        ),
        new OA\Parameter(
            name: 'to',
            in: 'query',
            schema: new OA\Schema(
                type: 'integer'
            ),
            description: 'Timestamp time'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'campaigns ip data',
        ),
        new OA\Response(
            response: 403,
            description: 'no campaigns ip found'
        ),
        new OA\Response(
            response: 500,
            description: 'campaigns Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class GetCampaignsIp extends BaseService
{
    /**
     * @var CampaignAction
     */
    private $campaignAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param CampaignAction $campaignAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(CampaignAction $campaignAction, Responder $responder)
    {
        $this->campaignAction = $campaignAction;
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

        $refId = (string)$args['id'];
        $params = $request->getQueryParams();
        $params['path'] = $request->getUri()->getPath();

        $result = $this->campaignAction->GetCampaignsIp($refId, $params);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
