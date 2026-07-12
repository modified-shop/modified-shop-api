<?php

/**
 * /includes/external/api/v1/Service/Webhook/GetSubscriptionDeliveries.php
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
    path: '/api/v1/webhooks/{Id}/deliveries',
    tags: ['Webhook'],
    summary: 'Get recent deliveries of a webhook subscription',
    description: 'Get the 50 most recent delivery attempts of a webhook subscription by given Id, '
        . 'newest first — status (pending, success, failed, skipped), attempt count, next retry '
        . 'time, last HTTP status and last error. Intended for debugging a receiver endpoint.',
    operationId: 'GetSubscriptionDeliveries',
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
    responses: [
        new OA\Response(
            response: 200,
            description: 'deliveries data',
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

final class GetSubscriptionDeliveries extends BaseService
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

        $result = $this->webhookAction->GetDeliveries($customersId, $subscriptionId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
