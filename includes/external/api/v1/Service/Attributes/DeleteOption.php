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

  #[OA\Delete(
    path: '/api/v1/attributes/options/{id}',
    tags: ['Attributes'],
    description: 'Delete attributes options by given Id',
    operationId: 'DeleteOption',
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
    responses:[
      new OA\Response(
        response: 204, 
        description: 'no data',
      ),
      new OA\Response(
        response: 403,
        description: 'options not found'
      ),
      new OA\Response(
        response: 500,
        description: 'options Id required'
      )
    ],
    security: [
      ['modified_auth' => ['DeleteOption']]
    ]
  )]

  final class DeleteOption extends BaseService
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
          
          $result = $this->attributesAction->DeleteOption($optionId);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response)->withStatus(204);
      }
  }
