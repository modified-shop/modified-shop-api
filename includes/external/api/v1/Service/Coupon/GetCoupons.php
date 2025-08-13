<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Coupon;

  use api\v1\Service\BaseService;
  use api\v1\Action\Coupon\CouponAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/coupons',
    tags: ['Coupon'],
    description: 'Get coupons data',
    operationId: 'GetCoupons',
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
          type: 'string',
          enum: ['Y', 'N']
        ),
        description: 'coupon active'
      ),
      new OA\Parameter(
        name: 'type', 
        in: 'query',
        schema: new OA\Schema(
          type: 'string'
        ),
        description: 'coupon type'
      ),
      new OA\Parameter(
        name: 'validfrom', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'timestamp coupon start date'
      ),
      new OA\Parameter(
        name: 'validto', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'timestamp coupon end date'
      ),
      new OA\Parameter(
        name: 'from', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'timestamp coupon date added'
      ),
      new OA\Parameter(
        name: 'to', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'timestamp coupon date added'
      )
    ],
    responses: [
      new OA\Response(
        response: 200, 
        description: 'countries data',
      ),
      new OA\Response(
        response: 403,
        description: 'countries not found'
      )
    ],
    security: [
      ['modified_auth' => ['GetCoupons']]
    ]
  )]
  
  final class GetCoupons extends BaseService
  {
      /**
       * @var CouponAction
       */
      private $couponAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param CouponAction $couponAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(CouponAction $couponAction, Responder $responder)
      {
          $this->couponAction = $couponAction;
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
          
          $result = $this->couponAction->GetCoupons($params);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
