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
     * Default access token lifetime in seconds (10 minutes), used when no
     * explicit lifetime is passed to the constructor. The live value normally
     * comes from settings.php (jwt.access_ttl) via the DI container.
     */
    public const DEFAULT_ACCESS_TTL = 600;

    /**
     * Default refresh token lifetime in seconds (30 days), used when no
     * explicit lifetime is passed to the constructor. The live value normally
     * comes from settings.php (jwt.refresh_ttl) via the DI container.
     */
    public const DEFAULT_REFRESH_TTL = 2592000;

    /**
     * Odds of opportunistically purging expired refresh tokens when a new one
     * is issued: on average one in this many issuances triggers the cleanup.
     */
    public const PURGE_ODDS = 100;

    /**
     * Grace period in seconds after a refresh token is rotated during which a
     * repeat presentation is treated as a benign concurrent double-submit
     * rather than token reuse. Prevents false-positive logouts from clients
     * that fire two refreshes at once, while keeping the theft window tiny.
     */
    public const REUSE_GRACE = 15;

    /**
     * @var int
     */
    private $accessTtl;

    /**
     * @var int
     */
    private $refreshTtl;

    /**
     * @var RefreshTokenRepository
     */
    private $refreshTokens;

    /**
     * The constructor.
     *
     * @param int $accessTtl Access token lifetime in seconds
     * @param int $refreshTtl Refresh token lifetime in seconds
     * @param RefreshTokenRepository|null $refreshTokens The refresh token store
     */
    public function __construct(
        int $accessTtl = self::DEFAULT_ACCESS_TTL,
        int $refreshTtl = self::DEFAULT_REFRESH_TTL,
        ?RefreshTokenRepository $refreshTokens = null
    ) {
        $this->accessTtl = $accessTtl;
        $this->refreshTtl = $refreshTtl;
        $this->refreshTokens = $refreshTokens ?? new RefreshTokenRepository();
    }

    /**
     * Issue an access token + refresh token pair for the given subject.
     *
     * @param string $sub The subject (customer email address) placed in the JWT
     * @param int $customersId The owning customer id (0 disables refresh issuance)
     * @param string $deviceId Opaque client-supplied device identifier to bind
     *                         the refresh token to (empty leaves it unbound)
     *
     * @return array<string,mixed> The OAuth token response payload
     */
    public function issue(string $sub, int $customersId, string $deviceId = ''): array
    {
        $secret = self::secret();
        $now = time();
        $exp = $now + $this->accessTtl;

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
            $refreshExp = $now + $this->refreshTtl;
            $this->refreshTokens->store($customersId, $refresh, $refreshExp, $now, $deviceId);

            $data['refresh_token'] = $refresh;
            $data['refresh_expires'] = $refreshExp;

            /* Opportunistic, amortized cleanup of expired rows (no cron needed): */
            /* only runs on a fraction of issuances to keep the table bounded. */
            if (random_int(1, self::PURGE_ODDS) === 1) {
                $this->refreshTokens->purgeExpired();
            }
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
