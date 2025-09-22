<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Product;

  use api\v1\Service\BaseService;
  use api\v1\Action\Product\ProductAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Post(
    path: '/api/v1/products/{Id}/content',
    tags: ['Product'],
    description: 'Insert products content by given Id',
    operationId: 'UpdateProductsContent',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'product Id'
      )
    ],
    responses: [
      new OA\Response(
        response: 201, 
        description: 'product content data',
      ),
      new OA\Response(
        response: 403,
        description: 'product not found'
      ),
      new OA\Response(
        response: 500,
        description: 'product Id required'
      )
    ],
    security: [
      ['modified_auth' => ['InsertUpdateContent']]
    ]
  )]

  #[OA\Put(
    path: '/api/v1/products/{Id}/content',
    tags: ['Product'],
    description: 'Update products content by given Id',
    operationId: 'InsertProductsContent',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'product Id'
      )
    ],
    responses: [
      new OA\Response(
        response: 201, 
        description: 'product content data',
      ),
      new OA\Response(
        response: 403,
        description: 'product not found'
      ),
      new OA\Response(
        response: 500,
        description: 'product Id required'
      )
    ],
    security: [
      ['modified_auth' => ['InsertUpdateContent']]
    ]
  )]

  final class InsertUpdateContent extends BaseService
  {
      /**
       * @var ProductAction
       */
      private $productAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param ProductAction $productAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(ProductAction $productAction, Responder $responder)
      {
          $this->productAction = $productAction;
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

          $productId = (int)$args['id'];
          $data = (array)$request->getParsedBody();
          
          $result = $this->productAction->InsertUpdateContent($productId, $data);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
