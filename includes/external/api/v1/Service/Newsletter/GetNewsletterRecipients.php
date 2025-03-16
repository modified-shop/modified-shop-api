<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Newsletter;

  use api\v1\Service\BaseService;
  use api\v1\Action\Newsletter\NewsletterAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  /**
   * Action
   */
  final class GetNewsletterRecipients extends BaseService
  {
      /**
       * @var NewsletterAction
       */
      private $newsletterAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param NewsletterAction $newsletterAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(NewsletterAction $newsletterAction, Responder $responder)
      {
          $this->newsletterAction = $newsletterAction;
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
          
          $result = $this->newsletterAction->GetNewsletterRecipients($params);

          return $this->responder->withJson($response, $result);
      }
  }
