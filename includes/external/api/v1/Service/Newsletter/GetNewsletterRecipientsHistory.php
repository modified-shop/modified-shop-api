<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Newsletter;

  use api\v1\Service\BaseService;
  use api\v1\Action\Newsletter\NewsletterAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/newsletters/recipients/history',
    tags: ['Newsletter'],
    description: 'Get newsletters recipients history data',
    operationId: 'GetNewsletterRecipientsHistory',
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
      )
    ],
    responses: [
      new OA\Response(
        response: 200, 
        description: 'newsletters recipients history data',
      ),
      new OA\Response(
        response: 403,
        description: 'no newsletters recipients history found'
      )
    ],
    security: [
      ['modified_auth' => ['GetNewsletterRecipientsHistory']]
    ]
  )]
  
  final class GetNewsletterRecipientsHistory extends BaseService
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

          $result = $this->newsletterAction->GetNewsletterRecipientsHistory($params);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
