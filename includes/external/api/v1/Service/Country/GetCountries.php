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
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/countries',
    tags: ['Country'],
    description: 'Get countries data',
    operationId: 'GetCountries',
    parameters: [
      new OA\Parameter(
        name: 'page', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'Number of page'
      ),
      new OA\Parameter(
        name: 'limit', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'Number of results per page'
      ),
      new OA\Parameter(
        name: 'status', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'status'
      ),
      new OA\Parameter(
        name: 'iso2', 
        in: 'query',
        schema: new OA\Schema(
          type: 'string'
        ),
        description: 'countries iso2 code'
      ),
      new OA\Parameter(
        name: 'iso2', 
        in: 'query',
        schema: new OA\Schema(
          type: 'string'
        ),
        description: 'countries iso3 code'
      ),
      new OA\Parameter(
        name: 'with', 
        in: 'query',
        schema: new OA\Schema(
          type: 'string'
        ),
        description: 'included results (comma separated list). Possible values: zones'
      )
    ],
    responses: [
      new OA\Response(
        response: 200, 
        description: 'countries data',
      ),
      new OA\Response(
        response: 403,
        description: 'countries not found'
      )
    ],
    security: [
      ['modified_auth' => ['GetCountries']]
    ]
  )]
  
  final class GetCountries extends BaseService
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

          $params = $request->getQueryParams();
          $params['path'] = $request->getUri()->getPath();
          
          $result = $this->countryAction->GetCountries($params);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
