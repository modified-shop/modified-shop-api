<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Language;

  use api\v1\Service\BaseService;
  use api\v1\Action\Language\LanguageAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  /**
   * Action
   */
  final class DeleteLanguage extends BaseService
  {
      /**
       * @var LanguageAction
       */
      private $languageAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param LanguageAction $languageAction The language reader
       * @param Responder $responder The responder
       */
      public function __construct(LanguageAction $languageAction, Responder $responder)
      {
          $this->languageAction = $languageAction;
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

          $languageId = (int)$args['id'];
          
          $this->languageAction->DeleteLanguage($languageId);

          return $this->responder->withJson($response)->withStatus(204);
      }
  }
