<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Category;

  use api\v1\Service\BaseService;
  use api\v1\Action\Category\CategoryAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Post(
    path: '/api/v1/categories/{Id}/image',
    tags: ['Category'],
    description: 'Insert categories image by given Id',
    operationId: 'InsertCategoriesImage',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'category Id'
      )
    ],
    responses:[
      new OA\Response(
        response: 201, 
        description: 'categories data',
      ),
      new OA\Response(
        response: 403,
        description: 'category not found'
      ),
      new OA\Response(
        response: 500,
        description: 'category Id required'
      )
    ],
    security: [
      ['modified_auth' => ['InsertUpdateImage']]
    ]
  )]

  #[OA\Put(
    path: '/api/v1/categories/{Id}/image',
    tags: ['Category'],
    description: 'Update categories image by given Id',
    operationId: 'UpdateCategoriesImage',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'category Id'
      )
    ],
    responses:[
      new OA\Response(
        response: 201, 
        description: 'categories data',
      ),
      new OA\Response(
        response: 403,
        description: 'category not found'
      ),
      new OA\Response(
        response: 500,
        description: 'category Id required'
      )
    ],
    security: [
      ['modified_auth' => ['InsertUpdateImages']]
    ]
  )]

  final class InsertUpdateImages extends BaseService
  {
      /**
       * @var CategoryAction
       */
      private $categoryAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param CategoryAction $categoryAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(CategoryAction $categoryAction, Responder $responder)
      {
          $this->categoryAction = $categoryAction;
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

          $categoryId = (int)$args['id'];
                    
          $result = $this->categoryAction->InsertUpdateImages($categoryId);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
