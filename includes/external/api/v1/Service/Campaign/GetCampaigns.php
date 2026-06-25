<?php

/**
 * /includes/external/api/v1/Service/Campaign/GetCampaigns.php
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
    path: '/api/v1/campaigns',
    tags: ['Campaign'],
    description: 'Get campaigns data',
    operationId: 'GetCampaigns',
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
      )
    ],
    responses: [
      new OA\Response(
        response: 200, 
        description: 'campaigns data',
      ),
      new OA\Response(
        response: 403,
        description: 'no campaigns found'
      )
    ],
    security: [
      ['modified_auth' => ['GetCampaigns']]
    ]
  )]
  
  final class GetCampaigns extends BaseService
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

          $params = $request->getQueryParams();
          $params['path'] = $request->getUri()->getPath();
          
          $result = $this->campaignAction->GetCampaigns($params);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
