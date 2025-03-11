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
  use Exception;

  /**
   * Action
   */
  final class DeleteProductAttributes extends BaseService
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

          $orderId = (int)$args['id'];
          $orderProductsAttributesId = (int)$args['aid'];

          // Input validation
          if (empty($orderProductsAttributesId)) {
              throw new Exception('Order products attributes ID required');
          }
          
          $this->orderAction->DeleteProductAttributes($orderId, $orderProductsAttributesId);

          return $this->responder->withJson($response)->withStatus(204);
      }
  }
