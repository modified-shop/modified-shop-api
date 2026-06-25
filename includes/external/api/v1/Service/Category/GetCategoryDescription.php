<?php

/**
 * /includes/external/api/v1/Service/Category/GetCategoryDescription.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Category;

  use api\v1\Service\BaseService;
  use api\v1\Action\Category\CategoryAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/categories/{Id}/description',
    tags: ['Category'],
    description: 'Get categories description by given Id',
    operationId: 'GetCategoryDescription',
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
    responses: [
      new OA\Response(
        response: 200, 
        description: 'category data',
      ),
      new OA\Response(
        response: 403,
        description: 'no category description found'
      ),
      new OA\Response(
        response: 500,
        description: 'category Id required'
      )
    ],
    security: [
      ['modified_auth' => ['GetCategoryDescription']]
    ]
  )]
  
  final class GetCategoryDescription extends BaseService
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

          $result = $this->categoryAction->GetCategoryDescription($categoryId);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
