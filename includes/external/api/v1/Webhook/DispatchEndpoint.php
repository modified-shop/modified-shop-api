<?php

/**
 * /includes/external/api/v1/Webhook/DispatchEndpoint.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Webhook;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/events/dispatch',
    tags: ['Webhook'],
    summary: 'Trigger the webhook dispatcher (cron)',
    description: 'Process the webhook queue: fan out new events to matching subscriptions and '
        . 'deliver due (including retried) webhook requests within a bounded time/batch budget. '
        . 'Meant to be called every few minutes by a hosting cron, e.g. '
        . 'curl -fsS -H "X-Dispatch-Secret: <secret>" https://shop.example/api/v1/events/dispatch. '
        . 'Authenticated by the static secret from the MODULE_API_ACCESS_WEBHOOKS_CRON_SECRET '
        . 'configuration value, sent as X-Dispatch-Secret header or secret query parameter — '
        . 'not by a JWT. POST is accepted as an alias for cron systems that cannot send GET. '
        . 'Returns run statistics; {"locked": true} means another dispatch run was already active.',
    operationId: 'DispatchEvents',
    parameters: [
        new OA\Parameter(
            name: 'secret',
            in: 'query',
            schema: new OA\Schema(
                type: 'string'
            ),
            description: 'Cron secret (alternative to the X-Dispatch-Secret header)'
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'dispatch run statistics',
        ),
        new OA\Response(
            response: 403,
            description: 'webhooks disabled or invalid cron secret'
        )
    ],
    security: []
)]

final class DispatchEndpoint
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * The constructor.
     *
     * @param Dispatcher $dispatcher The webhook dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
        array $args = []
    ): ResponseInterface {
        if (!defined('MODULE_API_ACCESS_WEBHOOKS_STATUS') || MODULE_API_ACCESS_WEBHOOKS_STATUS != 'true') {
            return $this->error($response, 'Webhooks are disabled');
        }

        $secret = defined('MODULE_API_ACCESS_WEBHOOKS_CRON_SECRET') ? (string)MODULE_API_ACCESS_WEBHOOKS_CRON_SECRET : '';

        $given = $request->getHeaderLine('X-Dispatch-Secret');
        if ($given === '') {
            $queryParams = $request->getQueryParams();
            $given = (string)($queryParams['secret'] ?? '');
        }

        if ($secret === '' || $given === '' || !hash_equals($secret, $given)) {
            return $this->error($response, 'Invalid dispatch secret');
        }

        $stats = $this->dispatcher->run();

        $response->getBody()->write((string)json_encode($stats));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Build a 403 JSON error response matching the API error format.
     *
     * @param ResponseInterface $response The response
     * @param string $message The error message
     *
     * @return ResponseInterface The response
     */
    private function error(ResponseInterface $response, string $message): ResponseInterface
    {
        $data = [
            'error' => [
                'message' => $message,
            ],
        ];

        $response->getBody()->write((string)json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }
}
