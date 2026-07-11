<?php

/**
 * /includes/external/api/v1/Service/Language/InsertUpdateLanguage.php
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

#[OA\Put(
    path: '/api/v1/languages/{Id}',
    tags: ['Language'],
    summary: 'Update single language data',
    description: 'Update single language data by given Id',
    operationId: 'InsertUpdatelanguage',
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
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                description: 'Columns of the languages database table, sent flat (e.g. code, which must be '
                    . 'unique). The exact set is installation-specific; discover it via '
                    . 'GET /api/v1/schema/languages.'
            )
        )
    ),
    responses:[
        new OA\Response(
            response: 201,
            description: 'languages data',
        ),
        new OA\Response(
            response: 400,
            description: 'invalid code supplied'
        ),
        new OA\Response(
            response: 500,
            description: 'language Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateLanguage extends BaseService
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

        $languageId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $result = $this->languageAction->InsertUpdateLanguage($languageId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
