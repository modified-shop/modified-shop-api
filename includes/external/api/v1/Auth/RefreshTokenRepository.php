<?php

/**
 * /includes/external/api/v1/Auth/RefreshTokenRepository.php
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

/**
 * Persistence for opaque refresh tokens.
 *
 * Only a SHA-256 hash of each refresh token is stored, so a database leak does
 * not expose usable tokens. Rows can be revoked (logout / rotation) and expire.
 */
final class RefreshTokenRepository
{
    /**
     * Hash a plain refresh token for storage / lookup.
     *
     * @param string $token The plain refresh token
     *
     * @return string The SHA-256 hex hash
     */
    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Persist a new refresh token (stored hashed).
     *
     * @param int $customersId The owning customer id
     * @param string $token The plain refresh token
     * @param int $expiresAt Absolute expiry as a unix timestamp
     * @param int $createdAt Creation time as a unix timestamp
     * @param string $deviceId Opaque client-supplied device identifier; empty
     *                         when the client did not send one, which leaves
     *                         the token unbound (no device check on refresh)
     *
     * @return void
     */
    public function store(
        int $customersId,
        string $token,
        int $expiresAt,
        int $createdAt,
        string $deviceId = ''
    ): void {
        $data = [
            'customers_id' => $customersId,
            'token_hash' => self::hash($token),
            'expires_at' => $expiresAt,
            'created_at' => $createdAt,
            'revoked' => 0,
            'device_id' => $deviceId,
        ];

        xtc_db_perform('api_refresh_tokens', $data);
    }

    /**
     * Look up a non-revoked, non-expired refresh token.
     *
     * @param string $token The plain refresh token
     *
     * @return array<string,mixed>|null The row when valid, otherwise null
     */
    public function findValid(string $token): ?array
    {
        $query = xtc_db_query("SELECT *
                                   FROM `api_refresh_tokens`
                                  WHERE token_hash = '" . xtc_db_input(self::hash($token)) . "'
                                    AND revoked = 0
                                    AND expires_at > '" . time() . "'
                                  LIMIT 1");
        $row = xtc_db_fetch_array($query);

        return is_array($row) ? $row : null;
    }

    /**
     * Look up a refresh token regardless of its revoked / expired state.
     *
     * Used by logout, where an already-expired (but not yet revoked) token must
     * still resolve to its owner so the session(s) can be revoked.
     *
     * @param string $token The plain refresh token
     *
     * @return array<string,mixed>|null The row when it exists, otherwise null
     */
    public function findByToken(string $token): ?array
    {
        $query = xtc_db_query("SELECT *
                                   FROM `api_refresh_tokens`
                                  WHERE token_hash = '" . xtc_db_input(self::hash($token)) . "'
                                  LIMIT 1");
        $row = xtc_db_fetch_array($query);

        return is_array($row) ? $row : null;
    }

    /**
     * Revoke a single refresh token by its row id.
     *
     * @param int $id The refresh token row id
     *
     * @return void
     */
    public function revokeById(int $id): void
    {
        xtc_db_query("UPDATE `api_refresh_tokens`
                         SET revoked = 1,
                             revoked_at = '" . time() . "'
                       WHERE id = '" . (int)$id . "'
                         AND revoked = 0");
    }

    /**
     * Revoke every refresh token belonging to a customer (e.g. full logout).
     *
     * @param int $customersId The customer id
     *
     * @return void
     */
    public function revokeAllForCustomer(int $customersId): void
    {
        xtc_db_query("UPDATE `api_refresh_tokens`
                         SET revoked = 1,
                             revoked_at = '" . time() . "'
                       WHERE customers_id = '" . (int)$customersId . "'
                         AND revoked = 0");
    }

    /**
     * Delete refresh tokens whose lifetime has ended.
     *
     * Only rows past their expiry are removed. Revoked-but-not-yet-expired
     * tokens are intentionally kept until they expire, so the row remains
     * available for refresh-token reuse detection.
     *
     * @return void
     */
    public function purgeExpired(): void
    {
        xtc_db_query("DELETE FROM `api_refresh_tokens`
                       WHERE expires_at < '" . time() . "'");
    }
}
