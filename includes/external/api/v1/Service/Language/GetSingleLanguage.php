<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Service\Language;

  use api\v1\Service\BaseService;
  use api\v1\Action\Language\LanguageAction;
  use api\v1\Utility\Responder;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use OpenApi\Attributes as OA;

  #[OA\Get(
    path: '/api/v1/languages/{Id}',
    tags: ['Language'],
    description: 'Get single language data by given Id',
    operationId: 'GetSinglelanguage',
    parameters: [
      new OA\Parameter(
        name: 'Id', 
        in: 'path',
        required: true,
        schema: new OA\Schema(
          type: 'integer',
        ),
        description: 'languages Id'
      )
    ],
    responses:[
      new OA\Response(
        response: 200, 
        description: 'languages data',
      ),
      new OA\Response(
          response: 400,
          description: 'invalid ID supplied'
      ),
      new OA\Response(
          response: 403,
          description: 'language not found'
      ),
      new OA\Response(
          response: 500,
          description: 'language Id required'
      )
    ],
    security: [
      ['modified_auth' => ['GetSinglelanguage']]
    ]
  )]

  final class GetSingleLanguage extends BaseService
  {
      /**
       * @var LanguageAction
       */
      private $languageAction;

      /**
       * @var Responder
       */
      private $responder;

      /**
       * The constructor.
       *
       * @param LanguageAction $languageAction The customer reader
       * @param Responder $responder The responder
       */
      public function __construct(LanguageAction $languageAction, Responder $responder)
      {
          $this->languageAction = $languageAction;
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

          $languageId = (int)$args['id'];
          
          $result = $this->languageAction->GetSingleLanguage($languageId);

          if (isset($result['errormessage'])) {
              return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
          }
          return $this->responder->withJson($response, $result);
      }
  }
