<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Schema;

  use api\v1\Service\BaseService;
  use api\v1\Action\Schema\SchemaAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/schema/{Table}',
    tags: ['Schema'],
    description: 'Get database schema by given table',
    operationId: 'GetSchema',
    parameters: [
      new OA\Parameter(
        name: 'Table', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'string',
        ),
        description: 'database table'
      )
    ],
    responses:[
      new OA\Response(
        response: 200, 
        description: 'table data',
      ),
      new OA\Response(
        response: 400,
        description: 'invalid table supplied'
      ),
      new OA\Response(
        response: 500,
        description: 'table required'
      )
    ],
    security: [
      ['modified_auth' => ['GetSchema']]
    ]
  )]

  final class GetSchema extends BaseService
  {
      /**
       * @var SchemaAction
       */
      private $SchemaAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param SchemaAction $SchemaAction The schema reader
       * @param Responder $responder The responder
       */
      public function __construct(SchemaAction $SchemaAction, Responder $responder)
      {
          $this->schemaAction = $SchemaAction;
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

          $table = (string)$args['table'];
          
          $result = $this->schemaAction->GetSchema($table);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
