<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Dhl;

  use api\v1\Service\BaseService;
  use api\v1\Action\Dhl\DhlAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  /**
   * Action
   */
  final class GetDhl extends BaseService
  {
      /**
       * @var DhlAction
       */
      private $dhlAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param DhlAction $dhlAction The order reader
       * @param Responder $responder The responder
       */
      public function __construct(DhlAction $dhlAction, Responder $responder)
      {
          $this->dhlAction = $dhlAction;
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
          $params = $request->getQueryParams();

          $result = $this->dhlAction->GetDhl($orderId, $params);

          return $this->responder->withJson($response, $result);
      }
  }
