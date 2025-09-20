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
  use Exception;
  use OpenApi\Attributes as OA;

  #[OA\Delete(
    path: '/api/v1/customers/{Id}/address_book/{aId}',
    tags: ['Customers'],
    description: 'Delete single customer by given Id',
    operationId: 'DeleteAddressBook',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'customer Id'
      ),
      new OA\Parameter(
        name: 'aId', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'address book Id'
      )
    ],
    responses:[
      new OA\Response(
        response: 204, 
        description: 'no data',
      ),
      new OA\Response(
        response: 403,
        description: 'customer not found'
      ),
      new OA\Response(
        response: 403,
        description: 'address book not found'
      ),
      new OA\Response(
        response: 500,
        description: 'customer Id required'
      ),
      new OA\Response(
        response: 500,
        description: 'address book Id required'
      )
    ],
    security: [
      ['modified_auth' => ['DeleteAddressBook']]
    ]
  )]

  final class DeleteAddressBook extends BaseService
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

          $customerId = (int)$args['id'];
          $addressBookId = (int)$args['aid'];
          
          // Input validation
          if (empty($addressBookId)) {
              throw new Exception('Address book ID required');
          }

          $result = $this->customerAction->DeleteAddressBook($customerId, $addressBookId);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response)->withStatus(204);
      }
  }
