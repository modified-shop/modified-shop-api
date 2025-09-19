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
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/tags/values',
    tags: ['Tags'],
    description: 'Get values data',
    operationId: 'GetTagsValues',
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
        name: 'option', 
        in: 'query',
        schema: new OA\Schema(
          type: 'string'
        ),
        description: 'option id (comma separated list)'
      )
    ],
    responses: [
      new OA\Response(
        response: 200, 
        description: 'values data',
      ),
      new OA\Response(
        response: 403,
        description: 'no value found'
      )
    ],
    security: [
      ['modified_auth' => ['GetValues']]
    ]
  )]
  
  final class GetValues extends BaseService
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

          $params = $request->getQueryParams();
          $params['path'] = $request->getUri()->getPath();
          
          $result = $this->tagsAction->GetValues($params);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
