<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Country;

  use api\v1\Service\BaseService;
  use api\v1\Action\Country\CountryAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  /**
   * Action
   */
  final class GetSingleTaxClass extends BaseService
  {
      /**
       * @var CountryAction
       */
      private $countryAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param CountryAction $countryAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(CountryAction $countryAction, Responder $responder)
      {
          $this->countryAction = $countryAction;
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

          $taxClassId = (int)$args['id'];

          $result = $this->countryAction->GetSingleTaxClass($taxClassId);

          return $this->responder->withJson($response, $result);
      }
  }
