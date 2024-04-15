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

  use api\v1\Action\DhlAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  /**
   * Action
   */
  final class DeleteDhl
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
          $orderId = (int)$args['id'];
          $params = $request->getQueryParams();

          $this->dhlAction->DeleteDhl($orderId, $params);

          return $this->responder->withJson($response)->withStatus(204);
      }
  }
