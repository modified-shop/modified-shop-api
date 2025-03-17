<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Currency;

  use api\v1\Service\BaseService;
  use api\v1\Action\Currency\CurrencyAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  /**
   * Action
   */
  final class GetSingleCurrency extends BaseService
  {
      /**
       * @var CurrencyAction
       */
      private $currencyAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param CurrencyAction $currencyAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(CurrencyAction $currencyAction, Responder $responder)
      {
          $this->currencyAction = $currencyAction;
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

          $currencyId = (int)$args['id'];
          $params = $request->getQueryParams();
          $params['path'] = $request->getUri()->getPath();

          $result = $this->currencyAction->GetSingleCurrency($currencyId, $params);

          return $this->responder->withJson($response, $result);
      }
  }
