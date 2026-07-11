<?php

/**
 * /includes/external/api/v1/Auth/JwtAuth.php
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
    path: '/api/v1/oauth',
    tags: ['Auth'],
    summary: 'Obtain a JWT access token',
    description: 'Obtain a JWT access token. Credentials can be sent either as request headers '
        . '(username, password) or as form fields (username, password). The returned access token is '
        . 'valid for 10 minutes and must be sent as a Bearer token on all protected endpoints. '
        . 'The response also contains a longer-lived refresh_token which can be exchanged for a new '
        . 'access token at /api/v1/oauth/refresh without re-sending the credentials. '
        . 'An optional device_id (header or form field) binds the refresh token to the calling device: '
        . 'once set, /api/v1/oauth/refresh will only accept that same device_id on later refreshes.',
    operationId: 'oauth',
    security: [],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/x-www-form-urlencoded',
            schema: new OA\Schema(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(
                        property: 'username',
                        type: 'string',
                        description: 'Customer email address with API access enabled'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        description: 'Customer password'
                    ),
                    new OA\Property(
                        property: 'device_id',
                        type: 'string',
                        description: 'Optional opaque device identifier to bind the refresh token to'
                    )
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'JWT access token'
        ),
        new OA\Response(
            response: 401,
            description: 'Authentication failed'
        )
    ]
)]

final class JwtAuth
{
    /**
     * @var TokenIssuer
     */
    private $tokenIssuer;

    /**
     * The constructor.
     *
     * @param TokenIssuer $tokenIssuer The access/refresh token issuer
     */
    public function __construct(TokenIssuer $tokenIssuer)
    {
        $this->tokenIssuer = $tokenIssuer;
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

        /* Subject may be provided as a request header (user/username) */
        /* or as a parsed body field (e.g. OAuth2 password grant from Swagger UI). */
        $usr = "";
        if ($user = $request->getHeaderLine("user")) {
            $usr = $user;
        }
        if ($user = $request->getHeaderLine("username")) {
            $usr = $user;
        }
        if ($usr === "") {
            $body = (array)$request->getParsedBody();
            if (isset($body["username"])) {
                $usr = (string)$body["username"];
            } elseif (isset($body["user"])) {
                $usr = (string)$body["user"];
            }
        }

        /* Optional device binding: may be sent as a header or a body field. */
        $deviceId = $request->getHeaderLine("device_id");
        if ($deviceId === "") {
            $body = (array)$request->getParsedBody();
            if (isset($body["device_id"])) {
                $deviceId = (string)$body["device_id"];
            }
        }

        /* Resolve the customer id so the refresh token can be tied to the */
        /* account. Credentials are already verified by the Authentication */
        /* middleware, so the customer is guaranteed to exist here. */
        $id_query = xtc_db_query("SELECT customers_id
                                      FROM " . TABLE_CUSTOMERS . "
                                     WHERE customers_email_address = '" . xtc_db_input($usr) . "'
                                     LIMIT 1");
        $id_row = xtc_db_fetch_array($id_query);
        $customersId = isset($id_row['customers_id']) ? (int)$id_row['customers_id'] : 0;

        $data = $this->tokenIssuer->issue($usr, $customersId, $deviceId);

        // Build the HTTP response
        $response->getBody()->write((string)json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
