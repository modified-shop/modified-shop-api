<?php

/**
 * /includes/external/api/v1/Service/Newsletter/InsertUpdateNewsletterRecipients.php
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

#[OA\Put(
    path: '/api/v1/newsletters/recipients/{Id}',
    tags: ['Newsletter'],
    summary: 'Update newsletters recipients data',
    description: 'Update newsletters recipients data by given Id',
    operationId: 'InsertUpdateNewsletter',
    parameters:[
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'newsletters Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                description: 'Columns of the newsletter_recipients database table, sent flat. The exact set is '
                    . 'installation-specific; discover it via GET /api/v1/schema/newsletter_recipients.'
            )
        )
    ),
    responses:[
        new OA\Response(
            response: 201,
            description: 'newsletters recipients data',
        ),
        new OA\Response(
            response: 403,
            description: 'newsletter recipients not found'
        ),
        new OA\Response(
            response: 500,
            description: 'newsletter Id required'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertUpdateNewsletterRecipients extends BaseService
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

        $newsletterId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $result = $this->newsletterAction->InsertUpdateNewsletterRecipients($newsletterId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
