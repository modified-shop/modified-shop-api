<?php

/**
 * /includes/external/api/v1/Auth/RateLimiter.php
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
 * Simple database-backed sliding-window rate limiter.
 *
 * Counts failed attempts per opaque key within a time window and locks the key
 * out for a cooldown once a threshold is reached. Used to throttle credential
 * brute-force on the authentication endpoints.
 */
final class RateLimiter
{
    /**
     * Fetch the counter row for a key.
     *
     * @param string $key The throttle key
     *
     * @return array<string,mixed>|null The row, or null when none exists
     */
    private static function row(string $key): ?array
    {
        $query = xtc_db_query("SELECT *
                                   FROM `api_rate_limit`
                                  WHERE rl_key = '" . xtc_db_input($key) . "'
                                  LIMIT 1");
        $row = xtc_db_fetch_array($query);

        return is_array($row) ? $row : null;
    }

    /**
     * Remaining lockout in seconds for a key.
     *
     * @param string $key The throttle key
     *
     * @return int Seconds until the key is unblocked, or 0 when not blocked
     */
    public function retryAfter(string $key): int
    {
        $row = self::row($key);
        if ($row === null) {
            return 0;
        }

        $remaining = (int)$row['blocked_until'] - time();

        return $remaining > 0 ? $remaining : 0;
    }

    /**
     * Record a failed attempt, locking the key once the threshold is hit.
     *
     * @param string $key The throttle key
     * @param int $maxAttempts Failures allowed within the window before lockout
     * @param int $window Sliding window length in seconds
     * @param int $lockout Lockout duration in seconds once tripped
     *
     * @return void
     */
    public function registerFailure(string $key, int $maxAttempts, int $window, int $lockout): void
    {
        $now = time();
        $row = self::row($key);

        /* First failure for this key. */
        if ($row === null) {
            xtc_db_perform('api_rate_limit', [
                'rl_key' => $key,
                'attempts' => 1,
                'window_start' => $now,
                'blocked_until' => 0,
            ]);
            return;
        }

        /* Window elapsed: start a fresh count. */
        if ($now - (int)$row['window_start'] > $window) {
            xtc_db_query("UPDATE `api_rate_limit`
                             SET attempts = 1,
                                 window_start = '" . $now . "',
                                 blocked_until = 0
                           WHERE rl_key = '" . xtc_db_input($key) . "'");
            return;
        }

        $attempts = (int)$row['attempts'] + 1;
        $blockedUntil = ($attempts >= $maxAttempts)
            ? $now + $lockout
            : (int)$row['blocked_until'];

        xtc_db_query("UPDATE `api_rate_limit`
                         SET attempts = '" . (int)$attempts . "',
                             blocked_until = '" . (int)$blockedUntil . "'
                       WHERE rl_key = '" . xtc_db_input($key) . "'");
    }

    /**
     * Clear the counter for a key (e.g. after a successful authentication).
     *
     * @param string $key The throttle key
     *
     * @return void
     */
    public function clear(string $key): void
    {
        xtc_db_query("DELETE FROM `api_rate_limit`
                       WHERE rl_key = '" . xtc_db_input($key) . "'");
    }

    /**
     * Delete stale counters that are no longer blocked and past their window.
     *
     * @param int $olderThan Age in seconds beyond the window before a row is dropped
     *
     * @return void
     */
    public function purgeStale(int $olderThan): void
    {
        xtc_db_query("DELETE FROM `api_rate_limit`
                       WHERE blocked_until < '" . time() . "'
                         AND window_start < '" . (time() - $olderThan) . "'");
    }
}
