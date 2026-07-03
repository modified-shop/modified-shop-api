<?php

/**
 * /includes/external/api/v1/Auth/TokenIssuer.php
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

use Firebase\JWT\JWT;
use Tuupola\Base62;

/**
 * Issues the access token (short-lived JWT) plus an opaque refresh token.
 *
 * This is the single source of truth for token lifetimes and is shared by the
 * initial login (JwtAuth) and the refresh endpoint (RefreshToken).
 */
final class TokenIssuer
{
    /**
     * Access token lifetime in seconds (10 minutes).
     */
    public const ACCESS_TTL = 600;

    /**
     * Refresh token lifetime in seconds (30 days).
     */
    public const REFRESH_TTL = 2592000;

    /**
     * @var RefreshTokenRepository
     */
    private $refreshTokens;

    /**
     * The constructor.
     *
     * @param RefreshTokenRepository|null $refreshTokens The refresh token store
     */
    public function __construct(?RefreshTokenRepository $refreshTokens = null)
    {
        $this->refreshTokens = $refreshTokens ?? new RefreshTokenRepository();
    }

    /**
     * Issue an access token + refresh token pair for the given subject.
     *
     * @param string $sub The subject (customer email address) placed in the JWT
     * @param int $customersId The owning customer id (0 disables refresh issuance)
     *
     * @return array<string,mixed> The OAuth token response payload
     */
    public function issue(string $sub, int $customersId): array
    {
        $secret = self::secret();
        $now = time();
        $exp = $now + self::ACCESS_TTL;

        $payload = [
            'iat' => $now,
            'exp' => $exp,
            'jti' => (new Base62())->encode(random_bytes(16)),
            'sub' => $sub,
        ];

        $data = [
            'access_token' => JWT::encode($payload, $secret, 'HS256'),
            'token_type' => 'Bearer',
            'expires' => $exp,
        ];

        /* Only issue a refresh token when we know the owning customer, so it can */
        /* be tied to an account and revoked later. */
        if ($customersId > 0) {
            $refresh = (new Base62())->encode(random_bytes(32));
            $refreshExp = $now + self::REFRESH_TTL;
            $this->refreshTokens->store($customersId, $refresh, $refreshExp, $now);

            $data['refresh_token'] = $refresh;
            $data['refresh_expires'] = $refreshExp;
        }

        return $data;
    }

    /**
     * Resolve and validate the configured signing secret.
     *
     * @throws \RuntimeException When the API module is not installed
     *
     * @return string The signing secret
     */
    public static function secret(): string
    {
        if (
            !defined('MODULE_API_ACCESS_SECRET')
            || empty(MODULE_API_ACCESS_SECRET)
        ) {
            throw new \RuntimeException("modified API not installed");
        }

        return MODULE_API_ACCESS_SECRET;
    }
}
