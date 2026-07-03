<?php

/**
 * /includes/external/api/v1/Auth/RateLimitMiddleware.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

declare(strict_types=1);

namespace api\v1\Auth;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Throttles the authentication endpoints against brute-force.
 *
 * The wrapped handler is expected to answer 401 on a failed attempt and 200 on
 * success. Failures increment per-IP and (for logins) per-account counters;
 * once a threshold is hit the key is locked out and further requests get 429
 * until the cooldown elapses. A success clears the counters.
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * Failed attempts per account (username) within the window before lockout.
     */
    public const ACCOUNT_MAX = 5;

    /**
     * Failed attempts per source IP before lockout (higher, to tolerate shared
     * addresses / NAT).
     */
    public const IP_MAX = 30;

    /**
     * Sliding window length in seconds.
     */
    public const WINDOW = 900;

    /**
     * Lockout duration in seconds once a threshold is tripped.
     */
    public const LOCKOUT = 900;

    /**
     * Odds of opportunistically purging stale counters (one in this many hits).
     */
    public const PURGE_ODDS = 100;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var RateLimiter
     */
    private $limiter;

    /**
     * The constructor.
     *
     * @param ResponseFactoryInterface $responseFactory The response factory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->limiter = new RateLimiter();
    }

    /**
     * Process a request in PSR-15 style and return a response.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /* Build the set of throttle keys with their per-key limits. A login */
        /* carries a username (per-account key); a refresh does not (IP only). */
        $keys = ['ip:' . $this->clientIp($request) => self::IP_MAX];

        $username = $this->username($request);
        if ($username !== '') {
            $keys['user:' . strtolower($username)] = self::ACCOUNT_MAX;
        }

        /* If any key is currently locked, reject before doing any work. */
        $retryAfter = 0;
        foreach ($keys as $key => $max) {
            $remaining = $this->limiter->retryAfter($key);
            if ($remaining > $retryAfter) {
                $retryAfter = $remaining;
            }
        }
        if ($retryAfter > 0) {
            return $this->tooManyRequests($retryAfter);
        }

        $response = $handler->handle($request);
        $status = $response->getStatusCode();

        if ($status === 401) {
            foreach ($keys as $key => $max) {
                $this->limiter->registerFailure($key, $max, self::WINDOW, self::LOCKOUT);
            }
        } elseif ($status === 200) {
            foreach ($keys as $key => $max) {
                $this->limiter->clear($key);
            }
        }

        /* Amortized cleanup of stale counters (no cron needed). */
        if (random_int(1, self::PURGE_ODDS) === 1) {
            $this->limiter->purgeStale(self::WINDOW);
        }

        return $response;
    }

    /**
     * Resolve the client IP from the server parameters.
     *
     * Note: behind a reverse proxy REMOTE_ADDR is the proxy; configure the proxy
     * to pass the real client IP if per-IP throttling should be per-client.
     *
     * @param ServerRequestInterface $request The request
     *
     * @return string The client IP (or a placeholder when unknown)
     */
    private function clientIp(ServerRequestInterface $request): string
    {
        $params = $request->getServerParams();

        return (string)($params['REMOTE_ADDR'] ?? 'unknown');
    }

    /**
     * Extract the submitted username (mirrors the Authentication middleware).
     *
     * @param ServerRequestInterface $request The request
     *
     * @return string The username, or an empty string when none was sent
     */
    private function username(ServerRequestInterface $request): string
    {
        $user = $request->getHeaderLine('username');
        if ($user === '') {
            $user = $request->getHeaderLine('user');
        }
        if ($user === '') {
            $body = (array)$request->getParsedBody();
            if (isset($body['username'])) {
                $user = (string)$body['username'];
            } elseif (isset($body['user'])) {
                $user = (string)$body['user'];
            }
        }

        return $user;
    }

    /**
     * Build a 429 JSON response with a Retry-After header.
     *
     * @param int $retryAfter Seconds until the caller may retry
     *
     * @return ResponseInterface The response
     */
    private function tooManyRequests(int $retryAfter): ResponseInterface
    {
        $data = [
            'error' => [
                'message' => 'Too many attempts. Please try again later.',
            ],
        ];

        $response = $this->responseFactory->createResponse(429);
        $response->getBody()->write((string)json_encode($data));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Retry-After', (string)$retryAfter);
    }
}
