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

  #[OA\Post(
    path: '/api/v1/contents',
    tags: ['Content'],
    description: 'Insert single content',
    operationId: 'InsertContent',
    responses:[
      new OA\Response(
        response: 201, 
        description: 'content data',
      )
    ],
    security: [
      ['modified_auth' => ['InsertContent']]
    ]
  )]

  final class InsertContent extends BaseService
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
       * @param ContentAction $contentAction The content reader
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

          $data = (array)$request->getParsedBody();
                    
          $result = $this->contentAction->InsertContent($data);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result)->withStatus(201);
      }
  }
