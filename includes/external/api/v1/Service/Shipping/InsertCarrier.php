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

  #[OA\Post(
    path: '/api/v1/shipping/carrier',
    tags: ['Shipping'],
    description: 'Insert single carrier',
    operationId: 'InsertCarrier',
    responses:[
      new OA\Response(
        response: 201, 
        description: 'carriers data',
      )
    ],
    security: [
      ['modified_auth' => ['InsertCarrier']]
    ]
  )]

  final class InsertCarrier extends BaseService
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

          $data = (array)$request->getParsedBody();
                    
          $result = $this->shippingAction->InsertCarrier($data);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result)->withStatus(201);
      }
  }
