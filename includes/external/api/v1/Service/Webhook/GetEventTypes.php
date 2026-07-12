<?php

/**
 * /includes/external/api/v1/Service/Webhook/GetEventTypes.php
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
    path: '/api/v1/webhooks/event_types',
    tags: ['Webhook'],
    summary: 'Get available webhook event types',
    description: 'Get all webhook event types, the api_access permission each one requires and '
        . 'whether the authenticated account currently holds it (allowed).',
    operationId: 'GetEventTypes',
    responses: [
        new OA\Response(
            response: 200,
            description: 'event types data',
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

final class GetEventTypes extends BaseService
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

        $result = $this->webhookAction->GetEventTypes($customersId);

        return $this->responder->withJson($response, $result);
    }
}
