<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Customer;

  use api\v1\Service\BaseService;
  use api\v1\Action\Customer\CustomerAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  /**
   * Action
   */
  final class InsertUpdateCustomer extends BaseService
  {
      /**
       * @var CustomerAction
       */
      private $customerAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param CustomerAction $customerAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(CustomerAction $customerAction, Responder $responder)
      {
          $this->customerAction = $customerAction;
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

          $customerId = ((isset($args['id'])) ? (int)$args['id'] : 0);
          $data = (array)$request->getParsedBody();
                    
          $result = $this->customerAction->InsertUpdateCustomer($customerId, $data);

          return $this->responder->withJson($response, $result)->withStatus(201);
      }
  }
