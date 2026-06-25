<?php

/**
 * /includes/external/api/v1/Service/Newsletter/DeleteNewsletterRecipients.php
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

#[OA\Delete(
    path: '/api/v1/newsletters/recipients/{Id}',
    tags: ['Newsletter'],
    description: 'Delete single newsletter data by given Id',
    operationId: 'DeleteNewsletterRecipients',
    parameters: [
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
    responses:[
        new OA\Response(
            response: 204,
            description: 'no data',
        ),
        new OA\Response(
            response: 403,
            description: 'newsletter recipient not found'
        ),
        new OA\Response(
            response: 500,
            description: 'newsletter Id required'
        )
    ],
    security: [
        ['modified_auth' => ['DeleteNewsletterRecipients']]
    ]
)]

final class DeleteNewsletterRecipients extends BaseService
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

        $result = $this->newsletterAction->DeleteNewsletterRecipients($newsletterId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response)->withStatus(204);
    }
}
