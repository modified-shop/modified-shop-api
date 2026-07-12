<?php

/**
 * /includes/external/api/v1/Service/Webhook/UpdateSubscription.php
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

#[OA\Put(
    path: '/api/v1/webhooks/{Id}',
    tags: ['Webhook'],
    summary: 'Update webhook subscription',
    description: 'Update url, event_types and/or active of an own webhook subscription by given '
        . 'Id. Setting active to true re-enables an auto-disabled subscription and resets its '
        . 'failure counter. The signing secret cannot be changed or retrieved.',
    operationId: 'UpdateSubscription',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'subscription Id'
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
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
                    new OA\Property(
                        property: 'active',
                        type: 'boolean',
                        description: 'Enable or disable the subscription'
                    ),
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'updated subscription data',
        ),
        new OA\Response(
            response: 400,
            description: 'invalid url, unknown event type or no updatable field given'
        ),
        new OA\Response(
            response: 403,
            description: 'missing api_access permission for a subscribed event type'
        ),
        new OA\Response(
            response: 404,
            description: 'subscription not found'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class UpdateSubscription extends BaseService
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
        $subscriptionId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $result = $this->webhookAction->UpdateSubscription($customersId, $subscriptionId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
