<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Campaign;

  use api\v1\Service\BaseService;
  use api\v1\Action\Campaign\CampaignAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Put(
    path: '/api/v1/campaigns/{Id}',
    tags: ['Campaign'],
    description: 'Update single campaigns data by given Id',
    operationId: 'InsertUpdateCampaign',
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
    ],
    responses:[
      new OA\Response(
        response: 201, 
        description: 'campaigns data',
      ),
      new OA\Response(
        response: 400,
        description: 'invalid refId supplied'
      ),
      new OA\Response(
        response: 500,
        description: 'campaigns Id required'
      )
    ],
    security: [
      ['modified_auth' => ['InsertUpdateCampaign']]
    ]
  )]

  final class InsertUpdateCampaign extends BaseService
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

          $campaignId = (int)$args['id'];
          $data = (array)$request->getParsedBody();
                    
          $result = $this->campaignAction->InsertUpdateCampaign($campaignId, $data);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
