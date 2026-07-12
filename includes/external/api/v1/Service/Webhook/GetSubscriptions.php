<?php

/**
 * /includes/external/api/v1/Service/Webhook/GetSubscriptions.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Webhook;

use api\v1\Service\BaseService;
use api\v1\Action\Webhook\WebhookAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/webhooks',
    tags: ['Webhook'],
    summary: 'Get own webhook subscriptions',
    description: 'Get all webhook subscriptions of the authenticated account. The subscription '
        . 'secret is never included; it is only returned once when the subscription is created.',
    operationId: 'GetSubscriptions',
    responses: [
        new OA\Response(
            response: 200,
            description: 'subscriptions data',
        ),
        new OA\Response(
            response: 401,
            description: 'invalid access token'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class GetSubscriptions extends BaseService
{
    /**
     * @var WebhookAction
     */
    private $webhookAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param WebhookAction $webhookAction The webhook action
     * @param Responder $responder The responder
     */
    public function __construct(WebhookAction $webhookAction, Responder $responder)
    {
        $this->webhookAction = $webhookAction;
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

        $token = $request->getAttribute('token');
        $customersId = $this->webhookAction->ResolveCustomersId((string)($token['sub'] ?? ''));

        $result = $this->webhookAction->GetSubscriptions($customersId);

        return $this->responder->withJson($response, $result);
    }
}
