<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Order;

  use api\v1\Service\BaseService;
  use api\v1\Action\Order\OrderAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/orders',
    tags: ['Order'],
    description: 'Get orders data',
    operationId: 'GetOrders',
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
        name: 'status', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'Status of order'
      ),
      new OA\Parameter(
        name: 'from', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'Timestamp date_purchased'
      ),
      new OA\Parameter(
        name: 'to', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'Timestamp date_purchased'
      ),
      new OA\Parameter(
        name: 'with', 
        in: 'query',
        schema: new OA\Schema(
          type: 'string'
        ),
        description: 'included results (comma separated list). Possible values: products, history, total, tracking'
      )
    ],
    responses: [
      new OA\Response(
        response: 200, 
        description: 'order data',
      ),
      new OA\Response(
        response: 403,
        description: 'no order found'
      )
    ],
    security: [
      ['modified_auth' => ['GetOrders']]
    ]
  )]
  
  final class GetOrders extends BaseService
  {
      /**
       * @var OrderAction
       */
      private $orderAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param OrderAction $orderAction The order reader
       * @param Responder $responder The responder
       */
      public function __construct(OrderAction $orderAction, Responder $responder)
      {
          $this->orderAction = $orderAction;
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
          
          $result = $this->orderAction->GetOrders($params);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
