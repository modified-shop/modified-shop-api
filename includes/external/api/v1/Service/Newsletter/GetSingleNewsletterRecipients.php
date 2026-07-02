<?php

/**
 * /includes/external/api/v1/Service/Newsletter/GetSingleNewsletterRecipients.php
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
    path: '/api/v1/newsletters/recipients/{Id}',
    tags: ['Newsletter'],
    description: 'Get single newsletters recipients data by given Id',
    operationId: 'GetSingleNewsletterRecipients',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'newsletters Id'
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
            description: 'no newsletters recipient found'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class GetSingleNewsletterRecipients extends BaseService
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

        $newsletterId = (int)$args['id'];

        $result = $this->newsletterAction->GetSingleNewsletterRecipients($newsletterId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
