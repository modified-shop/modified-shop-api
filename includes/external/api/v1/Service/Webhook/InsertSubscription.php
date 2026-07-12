<?php

/**
 * /includes/external/api/v1/Service/Webhook/InsertSubscription.php
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

#[OA\Post(
    path: '/api/v1/webhooks',
    tags: ['Webhook'],
    summary: 'Insert webhook subscription',
    description: 'Insert a webhook subscription for the authenticated account. The url must be '
        . 'https and each event type requires the matching '
        . 'api_access permission (see GET /api/v1/webhooks/event_types). The response contains '
        . 'the signing secret exactly once — store it now, it cannot be retrieved again. '
        . 'Deliveries are POST requests with headers X-Modified-Event, X-Modified-Delivery, '
        . 'X-Modified-Timestamp and X-Modified-Signature, where the signature is '
        . 'sha256=hex(HMAC-SHA256("{timestamp}.{raw body}", secret)). Receivers should reject '
        . 'timestamps older than a few minutes and compare signatures constant-time.',
    operationId: 'InsertSubscription',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                required: ['url', 'event_types'],
                properties: [
                    new OA\Property(
                        property: 'url',
                        type: 'string',
                        description: 'The https URL deliveries are POSTed to'
                    ),
                    new OA\Property(
                        property: 'event_types',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        description: 'Subscribed event types, e.g. ["order.created"]'
                    ),
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'subscription data including the one-time signing secret',
        ),
        new OA\Response(
            response: 400,
            description: 'invalid url, unknown event type or subscription limit reached'
        ),
        new OA\Response(
            response: 403,
            description: 'missing api_access permission for a subscribed event type'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertSubscription extends BaseService
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
        $data = (array)$request->getParsedBody();

        $result = $this->webhookAction->InsertSubscription($customersId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
