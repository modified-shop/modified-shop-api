<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Manufacturer;

  use api\v1\Service\BaseService;
  use api\v1\Action\Manufacturer\ManufacturerAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  /**
   * Action
   */
  final class InsertUpdateManufacturer extends BaseService
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

          $manufacturerId = ((isset($args['id'])) ? (int)$args['id'] : 0);
          $data = (array)$request->getParsedBody();
                    
          $result = $this->manufacturerAction->InsertUpdateManufacturer($manufacturerId, $data);
          
          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          if ($manufacturerId > 0) {
              return $this->responder->withJson($response, $result);
          }
          return $this->responder->withJson($response, $result)->withStatus(201);
      }
  }
