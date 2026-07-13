<?php

/**
 * /includes/external/api/v1/Webhook/UrlValidator.php
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

final class UrlValidator
{
    /**
     * Validate a webhook target URL at subscription time.
     *
     * https is required. Obvious internal targets (localhost, .local/
     * .internal hosts, private/reserved IP literals) are rejected as basic
     * SSRF protection. Hostnames that merely resolve to internal IPs are NOT
     * detected (accepted v1 residual risk — URLs are set by authenticated
     * merchant accounts); the dispatcher additionally disables redirects and
     * restricts curl to http/https.
     *
     * @param string $url The URL to validate
     *
     * @return string Empty string when valid, otherwise the error message
     */
    public static function validate(string $url): string
    {
        if ($url === '' || strlen($url) > 2048) {
            return 'url must be between 1 and 2048 characters';
        }

        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return 'url is not a valid absolute URL';
        }

        if (strtolower((string)$parts['scheme']) !== 'https') {
            return 'url must use https';
        }

        $host = strtolower(trim((string)$parts['host'], '[]'));
        if (
            $host === 'localhost'
            || str_ends_with($host, '.localhost')
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.internal')
        ) {
            return 'url host is not allowed';
        }

        if (
            filter_var($host, FILTER_VALIDATE_IP) !== false
            && filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
        ) {
            return 'url host must not be a private or reserved IP address';
        }

        return '';
    }
}
