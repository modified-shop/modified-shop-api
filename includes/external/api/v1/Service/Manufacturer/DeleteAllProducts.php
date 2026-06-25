<?php

/**
 * /includes/external/api/v1/Service/Manufacturer/DeleteAllProducts.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Manufacturer;

  use api\v1\Service\BaseService;
  use api\v1\Action\Manufacturer\ManufacturerAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Delete(
    path: '/api/v1/manufacturers/{Id}/products',
    tags: ['Manufacturer'],
    description: 'Delete all products from a manufacturer by given Id',
    operationId: 'DeleteAllProductsManufacturers',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'manufacturer Id'
      )
    ],
    responses:[
      new OA\Response(
        response: 204, 
        description: 'no data',
      ),
      new OA\Response(
        response: 403,
        description: 'manufacturer not found'
      ),
      new OA\Response(
        response: 500,
        description: 'manufacturer Id required'
      )
    ],
    security: [
      ['modified_auth' => ['DeleteAllProducts']]
    ]
  )]

  final class DeleteAllProducts extends BaseService
  {
      /**
       * @var ManufacturerAction
       */
      private $manufacturerAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param ManufacturerAction $manufacturerAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(ManufacturerAction $manufacturerAction, Responder $responder)
      {
          $this->manufacturerAction = $manufacturerAction;
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

          $manufacturerId = (int)$args['id'];
          
          $result = $this->manufacturerAction->DeleteAllProducts($manufacturerId);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response)->withStatus(204);
      }
  }
