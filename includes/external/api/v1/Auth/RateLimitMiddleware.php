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
     * IPs of trusted reverse proxies/load balancers allowed to set
     * X-Forwarded-For (see clientIp()).
     *
     * @var string[]
     */
    private $trustedProxies;

    /**
     * The constructor.
     *
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param string[] $trustedProxies IPs of trusted reverse proxies (empty = none, use REMOTE_ADDR as-is)
     */
    public function __construct(ResponseFactoryInterface $responseFactory, array $trustedProxies = [])
    {
        $this->responseFactory = $responseFactory;
        $this->limiter = new RateLimiter();
        $this->trustedProxies = $trustedProxies;
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
     * REMOTE_ADDR is only the real client when the request reached us
     * directly. Behind a reverse proxy it's the proxy's own address, and the
     * real client is in X-Forwarded-For instead - but that header is
     * attacker-controlled input, so it's only honored when REMOTE_ADDR
     * matches a proxy we explicitly configured to trust (see
     * $settings['trusted_proxies']). Without that, every client would share
     * one throttle key (self-inflicted lockout of all users), or worse, a
     * client could spoof X-Forwarded-For to evade its own throttling.
     *
     * @param ServerRequestInterface $request The request
     *
     * @return string The client IP (or a placeholder when unknown)
     */
    private function clientIp(ServerRequestInterface $request): string
    {
        $params = $request->getServerParams();
        $remoteAddr = (string)($params['REMOTE_ADDR'] ?? '');

        if ($remoteAddr === '' || !in_array($remoteAddr, $this->trustedProxies, true)) {
            return $remoteAddr !== '' ? $remoteAddr : 'unknown';
        }

        $forwardedFor = $request->getHeaderLine('X-Forwarded-For');
        $chain = array_values(array_filter(array_map('trim', explode(',', $forwardedFor))));

        /* Walk right-to-left (closest hop first): the first entry that isn't */
        /* itself one of our trusted proxies is the real client. */
        for ($i = count($chain) - 1; $i >= 0; $i--) {
            if (!in_array($chain[$i], $this->trustedProxies, true)) {
                return $chain[$i];
            }
        }

        return $remoteAddr;
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
