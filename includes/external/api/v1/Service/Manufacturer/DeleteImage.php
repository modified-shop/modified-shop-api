<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Manufacturer;

  use api\v1\Action\Manufacturer\ManufacturerAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  /**
   * Action
   */
  final class DeleteImage
  {
      /**
       * @var ManufacturerAction
       */
      private $manufacturerAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param ManufacturerAction $manufacturerAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(ManufacturerAction $manufacturerAction, Responder $responder)
      {
          $this->manufacturerAction = $manufacturerAction;
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
          $manufacturerId = (int)$args['id'];
          $imageId = (int)$args['iid'];
          
          $this->manufacturerAction->DeleteImage($manufacturerId, $imageId);

          return $this->responder->withJson($response)->withStatus(204);
      }
  }
