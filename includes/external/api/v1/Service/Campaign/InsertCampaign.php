<?php

/**
 * /includes/external/api/v1/Service/Campaign/InsertCampaign.php
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

#[OA\Post(
    path: '/api/v1/campaigns',
    tags: ['Campaign'],
    description: 'Insert single campaign',
    operationId: 'InsertCampaign',
    responses:[
        new OA\Response(
            response: 201,
            description: 'campaigns data',
        ),
        new OA\Response(
            response: 400,
            description: 'invalid refId supplied'
        )
    ],
    security: [
        ['modified_auth' => ['InsertCampaign']]
    ]
)]

final class InsertCampaign extends BaseService
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
     * @param CampaignAction $campaignAction The campaign reader
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

        $data = (array)$request->getParsedBody();

        $result = $this->campaignAction->InsertCampaign($data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
