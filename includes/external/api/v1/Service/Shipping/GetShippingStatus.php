<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Shipping;

  use api\v1\Service\BaseService;
  use api\v1\Action\Shipping\ShippingAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/shipping/status',
    tags: ['Shipping'],
    description: 'Get shipping status data',
    operationId: 'GetShippingStatus',
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
        description: 'shipping status data',
      ),
      new OA\Response(
        response: 403,
        description: 'no shipping status found'
      )
    ],
    security: [
      ['modified_auth' => ['GetShippingStatus']]
    ]
  )]
  
  final class GetShippingStatus extends BaseService
  {
      /**
       * @var ShippingAction
       */
      private $shippingAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param ShippingAction $shippingAction The shipping reader
       * @param Responder $responder The responder
       */
      public function __construct(ShippingAction $shippingAction, Responder $responder)
      {
          $this->shippingAction = $shippingAction;
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
          
          $result = $this->shippingAction->GetShippingStatus($params);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
