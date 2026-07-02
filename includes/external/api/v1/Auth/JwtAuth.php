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
use Firebase\JWT\JWT;
use Tuupola\Base62;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/oauth',
    tags: ['Auth'],
    description: 'Obtain a JWT access token. Credentials can be sent either as request headers '
        . '(username, password) or as form fields (username, password). The returned token is '
        . 'valid for 10 minutes and must be sent as a Bearer token on all protected endpoints.',
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
        if (
            !defined('MODULE_API_ACCESS_SECRET')
            || empty(MODULE_API_ACCESS_SECRET)
        ) {
            throw new \RuntimeException("modified API not installed");
        }

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

        $now = new \DateTime();
        $future = new \DateTime('+10 minutes');
        $server = $request->getServerParams();
        $jti = (new Base62())->encode(random_bytes(16));
        $payload = [
            'iat' => $now->getTimeStamp(),
            'exp' => $future->getTimeStamp(),
            'jti' => $jti,
            'sub' => $usr,
        ];
        $secret = MODULE_API_ACCESS_SECRET;
        $token = JWT::encode($payload, $secret, 'HS256');
        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires' => $future->getTimeStamp(),
        ];

        // Build the HTTP response
        $response->getBody()->write((string)json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
