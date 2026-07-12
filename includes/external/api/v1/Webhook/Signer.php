<?php

/**
 * /includes/external/api/v1/Webhook/Signer.php
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

final class Signer
{
    /**
     * Compute the webhook signature: HMAC-SHA256 over "{timestamp}.{body}".
     *
     * Signing the timestamp together with the body gives receivers replay
     * protection: they should reject requests whose X-Modified-Timestamp is
     * older than a few minutes and compare signatures constant-time.
     *
     * @param int $timestamp Unix time of the delivery attempt
     * @param string $body The raw JSON request body
     * @param string $secret The subscription secret
     *
     * @return string Lowercase hex digest
     */
    public static function sign(int $timestamp, string $body, string $secret): string
    {
        return hash_hmac('sha256', $timestamp . '.' . $body, $secret);
    }
}
