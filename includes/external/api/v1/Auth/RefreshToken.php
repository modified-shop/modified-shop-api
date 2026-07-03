<?php

/**
 * /includes/external/api/v1/Auth/RefreshToken.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use api\v1\Utility\LoggerHandler;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/oauth/refresh',
    tags: ['Auth'],
    description: 'Exchange a valid refresh token for a new access token without re-sending the '
        . 'user credentials. The refresh token can be sent either as a request header (refresh_token) '
        . 'or as a form field (refresh_token). On success a brand new access token and a new refresh '
        . 'token are returned; the presented refresh token is rotated (invalidated) in the process.',
    operationId: 'oauthRefresh',
    security: [],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/x-www-form-urlencoded',
            schema: new OA\Schema(
                required: ['refresh_token'],
                properties: [
                    new OA\Property(
                        property: 'refresh_token',
                        type: 'string',
                        description: 'A refresh token previously returned by /api/v1/oauth'
                    )
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'A new JWT access token and a rotated refresh token'
        ),
        new OA\Response(
            response: 401,
            description: 'Missing, unknown, expired or revoked refresh token'
        )
    ]
)]

final class RefreshToken
{
    /**
     * @var LoggerHandler
     */
    private $loggerHandler;

    /**
     * The constructor.
     *
     * @param LoggerHandler $loggerHandler The logger factory
     */
    public function __construct(LoggerHandler $loggerHandler)
    {
        $this->loggerHandler = $loggerHandler;
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
        /* Ensures the module is installed (throws otherwise). */
        TokenIssuer::secret();

        /* The refresh token may be sent as a request header or as a body field. */
        $refresh = "";
        if ($header = $request->getHeaderLine("refresh_token")) {
            $refresh = $header;
        }
        if ($refresh === "") {
            $body = (array)$request->getParsedBody();
            if (isset($body["refresh_token"])) {
                $refresh = (string)$body["refresh_token"];
            }
        }

        $repository = new RefreshTokenRepository();

        /* Look the token up in any state so we can tell an unknown token apart */
        /* from a known-but-already-rotated one (refresh token reuse). */
        $row = ($refresh === "") ? null : $repository->findByToken($refresh);

        if ($row === null) {
            /* Token was never issued (or already purged): nothing to act on. */
            return $this->unauthorized($response, "Invalid refresh token");
        }

        $now = time();
        $customersId = (int)$row['customers_id'];

        /* Expired token: reject, but this is not reuse. */
        if ((int)$row['expires_at'] <= $now) {
            return $this->unauthorized($response, "Invalid refresh token");
        }

        /* Revoked token presented again. */
        if ((int)$row['revoked'] === 1) {
            $revokedAt = isset($row['revoked_at']) ? (int)$row['revoked_at'] : 0;

            /* Within the grace window this is a benign concurrent double-submit */
            /* (client raced two refreshes); reject softly and keep the family. */
            /* The newer token the client already received stays valid. */
            if ($revokedAt > 0 && ($now - $revokedAt) <= TokenIssuer::REUSE_GRACE) {
                return $this->unauthorized($response, "Invalid refresh token");
            }

            /* Otherwise a rotated token is being replayed: treat as theft, */
            /* revoke every session of the account and log it for the merchant. */
            $repository->revokeAllForCustomer($customersId);
            $this->loggerHandler->createLogger()->warning(
                'Refresh token reuse detected; revoked all sessions',
                [
                    'customers_id' => $customersId,
                    'revoked_seconds_ago' => $revokedAt > 0 ? ($now - $revokedAt) : null,
                ]
            );

            return $this->unauthorized($response, "Invalid refresh token");
        }

        /* Re-check that the account still exists and still has API access; */
        /* the subject (email) is resolved fresh so it stays current. */
        $customer_query = xtc_db_query("SELECT c.customers_email_address
                                            FROM " . TABLE_CUSTOMERS . " c
                                            JOIN `api_access` aa
                                                 ON aa.customers_id = c.customers_id
                                           WHERE c.customers_id = '" . $customersId . "'
                                           LIMIT 1");
        $customer = xtc_db_fetch_array($customer_query);

        if (!isset($customer['customers_email_address'])) {
            /* Account gone or access removed: burn the token and reject. */
            $repository->revokeById((int)$row['id']);
            return $this->unauthorized($response, "Invalid refresh token");
        }

        /* Rotate: invalidate the presented refresh token and issue a fresh pair. */
        $repository->revokeById((int)$row['id']);
        $data = (new TokenIssuer($repository))->issue(
            (string)$customer['customers_email_address'],
            $customersId
        );

        $response->getBody()->write((string)json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Build a 401 JSON error response matching the API error format.
     *
     * @param ResponseInterface $response The response
     * @param string $message The error message
     *
     * @return ResponseInterface The response
     */
    private function unauthorized(ResponseInterface $response, string $message): ResponseInterface
    {
        $data = [
            'error' => [
                'message' => $message,
            ],
        ];

        $response->getBody()->write((string)json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}
