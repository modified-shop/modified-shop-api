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
  use Exception;
  use OpenApi\Attributes as OA;

  #[OA\Delete(
    path: '/api/v1/products/{Id}/categories/{cId}',
    tags: ['Product'],
    description: 'Delete single category from a product by given Id',
    operationId: 'DeleteProductCategory',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'product Id'
      ),
      new OA\Parameter(
        name: 'cId', 
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
        response: 204, 
        description: 'no data',
      ),
      new OA\Response(
        response: 403,
        description: 'product not found'
      ),
      new OA\Response(
        response: 403,
        description: 'category not found'
      ),
      new OA\Response(
        response: 500,
        description: 'product Id required'
      ),
      new OA\Response(
        response: 500,
        description: 'category Id required'
      )
    ],
    security: [
      ['modified_auth' => ['DeleteCategory']]
    ]
  )]

  final class DeleteCategory extends BaseService
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
          $categoryId = (int)$args['cid'];
          
          // Input validation
          if (empty($categoryId)) {
              throw new Exception('Category ID required');
          }

          $result = $this->productAction->DeleteCategory($productId, $categoryId);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response)->withStatus(204);
      }
  }
