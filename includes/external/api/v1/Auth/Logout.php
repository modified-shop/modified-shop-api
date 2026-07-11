<?php

/**
 * /includes/external/api/v1/Auth/Logout.php
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
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/oauth/logout',
    tags: ['Auth'],
    summary: 'Revoke a refresh token (logout)',
    description: 'Revoke a refresh token. The access token is short-lived and self-expires, so a '
        . 'logout revokes the refresh token: send it as a request header (refresh_token) or as a '
        . 'form field (refresh_token). Set the optional "all" flag to revoke every refresh token of '
        . 'the account (log out on all devices). The call is idempotent and always succeeds when a '
        . 'token is supplied, whether or not it was still valid.',
    operationId: 'oauthLogout',
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
                    ),
                    new OA\Property(
                        property: 'all',
                        type: 'boolean',
                        description: 'Revoke every refresh token of the account (all devices)'
                    )
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'The refresh token (or all of the account) was revoked'
        ),
        new OA\Response(
            response: 400,
            description: 'No refresh token supplied'
        )
    ]
)]

final class Logout
{
    /**
     * The constructor.
     */
    public function __construct()
    {
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

        $body = (array)$request->getParsedBody();

        /* The refresh token may be sent as a request header or as a body field. */
        $refresh = "";
        if ($header = $request->getHeaderLine("refresh_token")) {
            $refresh = $header;
        }
        if ($refresh === "" && isset($body["refresh_token"])) {
            $refresh = (string)$body["refresh_token"];
        }

        if ($refresh === "") {
            $data = ['error' => ['message' => 'refresh_token required']];
            $response->getBody()->write((string)json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        /* Whether to revoke every session of the account (all devices). */
        $all = false;
        if ($header = $request->getHeaderLine("all")) {
            $all = in_array(strtolower($header), ['1', 'true', 'yes'], true);
        }
        if (!$all && isset($body["all"])) {
            $all = in_array(strtolower((string)$body["all"]), ['1', 'true', 'yes'], true);
        }

        $repository = new RefreshTokenRepository();
        $row = $repository->findByToken($refresh);

        /* Idempotent: revoke whatever the token maps to, if anything. */
        if ($row !== null) {
            if ($all) {
                $repository->revokeAllForCustomer((int)$row['customers_id']);
            } else {
                $repository->revokeById((int)$row['id']);
            }
        }

        $response->getBody()->write((string)json_encode(['success' => true]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
