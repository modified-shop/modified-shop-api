<?php

/**
 * /includes/external/api/v1/Service/Currency/InsertCurrency.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Currency;

  use api\v1\Service\BaseService;
  use api\v1\Action\Currency\CurrencyAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Post(
    path: '/api/v1/currencies',
    tags: ['Currency'],
    description: 'Insert single currency',
    operationId: 'InsertCurrency',
    responses:[
      new OA\Response(
        response: 201, 
        description: 'currencies data',
      ),
      new OA\Response(
        response: 400,
        description: 'invalid code supplied'
      )
    ],
    security: [
      ['modified_auth' => ['InsertCurrency']]
    ]
  )]

  final class InsertCurrency extends BaseService
  {
      /**
       * @var CurrencyAction
       */
      private $currencyAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param CurrencyAction $currencyAction The currency reader
       * @param Responder $responder The responder
       */
      public function __construct(CurrencyAction $currencyAction, Responder $responder)
      {
          $this->currencyAction = $currencyAction;
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
                    
          $result = $this->currencyAction->InsertCurrency($data);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result)->withStatus(201);
      }
  }
