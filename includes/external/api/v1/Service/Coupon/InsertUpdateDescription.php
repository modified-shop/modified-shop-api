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

  /**
   * Action
   */
  final class InsertUpdateDescription extends BaseService
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

          $couponId = ((isset($args['id'])) ? (int)$args['id'] : 0);
          $data = (array)$request->getParsedBody();
                    
          $result = $this->couponAction->InsertUpdateDescription($couponId, $data);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
