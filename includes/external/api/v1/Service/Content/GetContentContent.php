<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Content;

  use api\v1\Service\BaseService;
  use api\v1\Action\Content\ContentAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/contents/{Id}/content',
    tags: ['Content'],
    description: 'Get content from a content by given Id',
    operationId: 'GetContentContent',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'content group Id'
      )
    ],
    responses: [
      new OA\Response(
        response: 200, 
        description: 'content data',
      ),
      new OA\Response(
        response: 403,
        description: 'no content content found'
      ),
      new OA\Response(
        response: 500,
        description: 'content group Id required'
      )
    ],
    security: [
      ['modified_auth' => ['GetContentContent']]
    ]
  )]
  
  final class GetContentContent extends BaseService
  {
      /**
       * @var ContentAction
       */
      private $contentAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param ContentAction $contentAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(ContentAction $contentAction, Responder $responder)
      {
          $this->contentAction = $contentAction;
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

          $contentGroupId = (int)$args['id'];

          $result = $this->contentAction->GetContentContent($contentGroupId);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
