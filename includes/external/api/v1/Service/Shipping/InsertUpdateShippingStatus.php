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

  #[OA\Put(
    path: '/api/v1/shipping/status/{Id}',
    tags: ['Shipping'],
    description: 'Update shipping status data by given Id',
    operationId: 'InsertUpdateShippingStatus',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'shipping status Id'
      )
    ],
    responses: [
      new OA\Response(
        response: 201, 
        description: 'shipping status data',
      ),
      new OA\Response(
        response: 403,
        description: 'shipping status not found'
      ),
      new OA\Response(
        response: 500,
        description: 'shipping status Id required'
      )
    ],
    security: [
      ['modified_auth' => ['InsertUpdateShippingStatus']]
    ]
  )]

  final class InsertUpdateShippingStatus extends BaseService
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

          $shippingStatusId = (int)$args['id'];
          $data = (array)$request->getParsedBody();
                    
          $result = $this->shippingAction->InsertUpdateShippingStatus($shippingStatusId, $data);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
