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
    path: '/api/v1/attributes/options/{Id}',
    tags: ['Attributes'],
    description: 'Get single attributes options by given Id',
    operationId: 'GetSingleOption',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'options Id'
      )
    ],
    responses: [
      new OA\Response(
        response: 200, 
        description: 'attributes options data',
      ),
      new OA\Response(
        response: 403,
        description: 'no options found'
      ),
      new OA\Response(
        response: 500,
        description: 'options Id required'
      )
    ],
    security: [
      ['modified_auth' => ['GetSingleOption']]
    ]
  )]
  
  final class GetSingleOption extends BaseService
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

          $optionId = (int)$args['id'];

          $result = $this->attributesAction->GetSingleOption($optionId);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
