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
    path: '/api/v1/countries/geo_zones/{Id}',
    tags: ['Country'],
    description: 'Get geo zones data by given Id',
    operationId: 'GetSingleGeoZone',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'geo zones Id'
      ),
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
        name: 'with', 
        in: 'query',
        schema: new OA\Schema(
          type: 'string'
        ),
        description: 'included results (comma separated list). Possible values: countries'
      )
    ],
    responses: [
      new OA\Response(
        response: 200, 
        description: 'geo zones data',
      ),
      new OA\Response(
        response: 403,
        description: 'geo zones not found'
      ),
      new OA\Response(
        response: 500,
        description: 'countries Id required'
      )
    ],
    security: [
      ['modified_auth' => ['GetSingleGeoZone']]
    ]
  )]
  
  final class GetSingleGeoZone extends BaseService
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

          $geoZoneId = (int)$args['id'];
          $params = $request->getQueryParams();
          $params['path'] = $request->getUri()->getPath();

          $result = $this->countryAction->GetSingleGeoZone($geoZoneId, $params);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
