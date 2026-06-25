<?php

/**
 * /includes/external/api/v1/Service/Newsletter/GetNewsletterRecipients.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Newsletter;

  use api\v1\Service\BaseService;
  use api\v1\Action\Newsletter\NewsletterAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/newsletters/recipients',
    tags: ['Newsletter'],
    description: 'Get newsletters recipients data',
    operationId: 'GetNewsletterRecipients',
    parameters: [
      new OA\Parameter(
        name: 'page', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'Number of page'
      ),
      new OA\Parameter(
        name: 'limit', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'Number of results per page'
      ),
      new OA\Parameter(
        name: 'status', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'Status of newsletters'
      ),
      new OA\Parameter(
        name: 'mail', 
        in: 'query',
        schema: new OA\Schema(
          type: 'string'
        ),
        description: 'customers email address'
      ),
      new OA\Parameter(
        name: 'from', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'Timestamp date_added'
      ),
      new OA\Parameter(
        name: 'to', 
        in: 'query',
        schema: new OA\Schema(
          type: 'integer'
        ),
        description: 'Timestamp date_added'
      ),
      new OA\Parameter(
        name: 'with', 
        in: 'query',
        schema: new OA\Schema(
          type: 'string'
        ),
        description: 'included results (comma separated list). Possible values: history'
      )
    ],
    responses: [
      new OA\Response(
        response: 200, 
        description: 'newsletters recipients data',
      ),
      new OA\Response(
        response: 403,
        description: 'no newsletters recipients found'
      )
    ],
    security: [
      ['modified_auth' => ['GetNewsletterRecipients']]
    ]
  )]
  
  final class GetNewsletterRecipients extends BaseService
  {
      /**
       * @var NewsletterAction
       */
      private $newsletterAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param NewsletterAction $newsletterAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(NewsletterAction $newsletterAction, Responder $responder)
      {
          $this->newsletterAction = $newsletterAction;
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

          $params = $request->getQueryParams();
          $params['path'] = $request->getUri()->getPath();
          
          $result = $this->newsletterAction->GetNewsletterRecipients($params);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
