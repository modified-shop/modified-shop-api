<?php

/**
 * /includes/external/api/v1/Service/Language/InsertLanguage.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Language;

use api\v1\Service\BaseService;
use api\v1\Action\Language\LanguageAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/languages',
    tags: ['Language'],
    description: 'Insert single language',
    operationId: 'Insertlanguage',
    responses:[
        new OA\Response(
            response: 201,
            description: 'languages data',
        ),
        new OA\Response(
            response: 400,
            description: 'invalid code supplied'
        )
    ],
    security: [
        ['modified_auth' => ['Insertlanguage']]
    ]
)]

final class InsertLanguage extends BaseService
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
     * @param LanguageAction $languageAction The language reader
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

        $data = (array)$request->getParsedBody();

        $result = $this->languageAction->InsertLanguage($data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
