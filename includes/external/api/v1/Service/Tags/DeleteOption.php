<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Tags;

  use api\v1\Service\BaseService;
  use api\v1\Action\Tags\TagsAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  /**
   * Action
   */
  final class DeleteOption extends BaseService
  {
      /**
       * @var TagsAction
       */
      private $tagsAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param TagsAction $tagsAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(TagsAction $tagsAction, Responder $responder)
      {
          $this->tagsAction = $tagsAction;
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

          $optionId = (int)$args['id'];
          
          $this->tagsAction->DeleteOption($optionId);

          return $this->responder->withJson($response)->withStatus(204);
      }
  }
