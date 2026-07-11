<?php

/**
 * /includes/external/api/v1/Service/Language/DeleteLanguage.php
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

#[OA\Delete(
    path: '/api/v1/languages/{Id}',
    tags: ['Language'],
    summary: 'Delete single language data',
    description: 'Delete single language data by given Id',
    operationId: 'DeleteLanguage',
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
            response: 204,
            description: 'no data',
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
        ['modified_auth' => []]
    ]
)]

final class DeleteLanguage extends BaseService
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

        $result = $this->languageAction->DeleteLanguage($languageId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response)->withStatus(204);
    }
}
