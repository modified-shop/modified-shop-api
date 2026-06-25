<?php

/**
 * /includes/external/api/v1/Service/Newsletter/InsertNewsletterRecipients.php
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

  #[OA\Post(
    path: '/api/v1/newsletters/recipients',
    tags: ['Newsletter'],
    description: 'Insert single newsletter recipients',
    operationId: 'InsertNewsletterRecipients',
    responses:[
      new OA\Response(
        response: 201, 
        description: 'newsletters recipients data',
      ),
      new OA\Response(
        response: 400,
        description: 'invalid email address supplied'
      )
    ],
    security: [
      ['modified_auth' => ['InsertNewsletterRecipients']]
    ]
  )]

  final class InsertNewsletterRecipients extends BaseService
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
       * @param NewsletterAction $newsletterAction The newsletter reader
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

          $data = (array)$request->getParsedBody();
                    
          $result = $this->newsletterAction->InsertNewsletterRecipients($data);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result)->withStatus(201);
      }
  }
