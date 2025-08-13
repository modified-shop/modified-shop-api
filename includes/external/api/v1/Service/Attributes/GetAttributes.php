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

  use api\v1\Service\BaseService;
  use api\v1\Action\Attributes\AttributesAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/attributes/{Id}',
    tags: ['Attribute'],
    description: 'Get attributes data by given Id',
    operationId: 'GetAttributes',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'options Id'
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
      )
    ],
    responses: [
      new OA\Response(
        response: 200, 
        description: 'attributes data',
      ),
      new OA\Response(
        response: 403,
        description: 'no attributes found'
      ),
      new OA\Response(
        response: 500,
        description: 'options Id required'
      )
    ],
    security: [
      ['modified_auth' => ['GetAttributes']]
    ]
  )]
  
  final class GetAttributes extends BaseService
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
          $this->CheckAccess($request, $response);

          $params = $request->getQueryParams();
          $params['path'] = $request->getUri()->getPath();

          $optionId = (int)$args['id'];

          $result = $this->attributesAction->GetAttributes($optionId, $params);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
