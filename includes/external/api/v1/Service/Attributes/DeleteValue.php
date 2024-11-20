<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Attributes;

  use api\v1\Action\AttributesAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  /**
   * Action
   */
  final class DeleteValue
  {
      /**
       * @var AttributesAction
       */
      private $attributesAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param AttributesAction $attributesAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(AttributesAction $attributesAction, Responder $responder)
      {
          $this->attributesAction = $attributesAction;
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
          $valueId = (int)$args['id'];
          
          $this->attributesAction->DeleteValue($valueId);

          return $this->responder->withJson($response)->withStatus(204);
      }
  }
