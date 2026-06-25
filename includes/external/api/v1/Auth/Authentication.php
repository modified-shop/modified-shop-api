<?php

/**
 * /includes/external/api/v1/Auth/Authentication.php
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

use Closure;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use SplStack;
use Tuupola\Http\Factory\ResponseFactory;
use Tuupola\Middleware\DoublePassTrait;
use Tuupola\Middleware\HttpBasicAuthentication\AuthenticatorInterface;
use Tuupola\Middleware\HttpBasicAuthentication\ArrayAuthenticator;
use Tuupola\Middleware\HttpBasicAuthentication\RequestMethodRule;
use Tuupola\Middleware\HttpBasicAuthentication\RequestPathRule;
use Tuupola\Middleware\HttpBasicAuthentication\RuleInterface;

/**
 * Action
 */
final class Authentication implements MiddlewareInterface
{
    use DoublePassTrait;

    /**
     * @var SplStack<RuleInterface>
     */
    private $rules;

    /**
     * @var mixed[]
     */
    private $options = [
        "secure" => true,
        "relaxed" => ["localhost", "127.0.0.1"],
        "authenticator" => null,
        "before" => null,
        "after" => null,
        "error" => null
    ];

    /**
     * @param mixed[] $options
     */
    public function __construct(array $options = [])
    {
        /* Setup stack for rules */
        $this->rules = new SplStack();

        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        /* If nothing was passed in options add default rules. */
        if (!isset($options["rules"])) {
            $this->rules->push(new RequestMethodRule([
                "ignore" => ["OPTIONS"]
            ]));
        }

        /* If array of users was passed in options create an authenticator */
        if (defined('MODULE_API_ACCESS_STATUS')
            && MODULE_API_ACCESS_STATUS == 'true'
        ) {
            $access_query = xtc_db_query("SELECT *
                                              FROM " . TABLE_CUSTOMERS . " c
                                              JOIN `api_access` aa
                                                   ON c.customers_id = aa.customers_id");
            if (xtc_db_num_rows($access_query) > 0) {
                $users = [];
                while ($access = xtc_db_fetch_array($access_query)) {
                    $users[$access['customers_email_address']] = $access['customers_password'];
                }
                $this->options["authenticator"] = new ArrayAuthenticator([
                    "users" => $users
                ]);
            }
        }

        /* There must be an authenticator either passed via options */
        /* or added because $this->options["users"] was an array. */
        if (null === $this->options["authenticator"]) {
            throw new \RuntimeException("modified API not installed");
        }
    }

    /**
     * Process a request in PSR-15 style and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $host = $request->getUri()->getHost();
        $scheme = $request->getUri()->getScheme();
        $server_params = $request->getServerParams();

        /* If rules say we should not authenticate call next and return. */
        if (false === $this->shouldAuthenticate($request)) {
            return $handler->handle($request);
        }

        /* HTTP allowed only if secure is false or server is in relaxed array. */
        if ("https" !== $scheme && true === $this->options["secure"]) {
            $allowedHost = in_array($host, $this->options["relaxed"]);

            /* if 'headers' is in the 'relaxed' key, then we check for forwarding */
            $allowedForward = false;
            if (in_array("headers", $this->options["relaxed"])) {
                if ($request->getHeaderLine("X-Forwarded-Proto") === "https"
                    && $request->getHeaderLine('X-Forwarded-Port') === "443"
                ) {
                    $allowedForward = true;
                }
            }

            if (!($allowedHost || $allowedForward)) {
                $message = sprintf(
                    "Insecure use of middleware over %s denied by configuration.",
                    strtoupper($scheme)
                );
                throw new \RuntimeException($message);
            }
        }

        /* Just in case. */
        $params = [
            "password" => $request->getHeaderLine("password")
        ];

        if ($user = $request->getHeaderLine("user")) {
            $params["user"] = $user;
        }
        if ($user = $request->getHeaderLine("username")) {
            $params["user"] = $user;
        }

        /* Check if user authenticates. */
        if (false === $this->options["authenticator"]($params)) {
            /* Set response headers before giving it to error callback */
            $response = (new ResponseFactory())->createResponse(401);

            return $this->processError($response, [
                "message" => "Authentication failed"
            ]);
        }

        /* Modify $request before calling next middleware. */
        if (is_callable($this->options["before"])) {
            $response = (new ResponseFactory())->createResponse(200);
            $before_request = $this->options["before"]($request, $params);
            if ($before_request instanceof ServerRequestInterface) {
                $request = $before_request;
            }
        }

        /* Everything ok, call next middleware. */
        $response = $handler->handle($request);

        /* Modify $response before returning. */
        if (is_callable($this->options["after"])) {
            $after_response = $this->options["after"]($response, $params);
            if ($after_response instanceof ResponseInterface) {
                return $after_response;
            }
        }

        return $response;
    }

    /**
     * Hydrate all options from given array.
     *
     * @param mixed[] $data
     */
    private function hydrate(array $data = []): void
    {
        foreach ($data as $key => $value) {
            $key = str_replace(".", " ", $key);
            $method = lcfirst(ucwords($key));
            $method = str_replace(" ", "", $method);
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $value);
            } else {
                $this->options[$key] = $value;
            }
        }
    }

    /**
     * Test if current request should be authenticated.
     */
    private function shouldAuthenticate(ServerRequestInterface $request): bool
    {
        /* If any of the rules in stack return false will not authenticate */
        foreach ($this->rules as $callable) {
            if (false === $callable($request)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Execute the error handler.
     *
     * @param mixed[] $arguments
     */
    private function processError(ResponseInterface $response, array $arguments): ResponseInterface
    {
        if (is_callable($this->options["error"])) {
            $handler_response = $this->options["error"]($response, $arguments);
            if ($handler_response instanceof ResponseInterface) {
                return $handler_response;
            }
        }
        return $response;
    }

    /**
     * Set path which middleware ignores.
     *
     * @param string[] $ignore
     */
    private function ignore($ignore): void
    {
        $this->options["ignore"] = (array) $ignore;
    }

    /**
     * Set the authenticator.
     */
    private function authenticator(callable $authenticator): void
    {
        $this->options["authenticator"] = $authenticator;
    }

    /**
     * Set the secure flag.
     */
    private function secure(bool $secure): void
    {
        $this->options["secure"] = $secure;
    }

    /**
     * Set hosts where secure rule is relaxed.
     *
     * @param string[] $relaxed
     */
    private function relaxed(array $relaxed): void
    {
        $this->options["relaxed"] = $relaxed;
    }

    /**
     * Set the handler which is called before other middlewares.
     */
    private function before(Closure $before): void
    {
        $this->options["before"] = $before->bindTo($this);
    }

    /**
     * Set the handler which is called after other middlewares.
     */
    private function after(Closure $after): void
    {
        $this->options["after"] = $after->bindTo($this);
    }

    /**
     * Set the handler which is if authentication fails.
     */
    private function error(callable $error): void
    {
        $this->options["error"] = $error;
    }

    /**
     * Set the rules
     *
     * @param RuleInterface[] $rules
     */
    private function rules(array $rules): void
    {
        $this->rules = new SplStack();
        foreach ($rules as $callable) {
            $this->rules->push($callable);
        }
    }

    /**
     * Set the rules which determine if current request should be authenticated.
     *
     * Rules must be callables which return a boolean. If any of the rules return
     * boolean false current request will not be authenticated.
     *
     * @param RuleInterface[] $rules
     */
    public function withRules(array $rules): self
    {
        $new = clone $this;
        /* Clear the stack */
        unset($new->rules);
        $new->rules = new SplStack();

        /* Add the rules */
        foreach ($rules as $callable) {
            $new = $new->addRule($callable);
        }
        return $new;
    }

    /**
     * Add a rule to the rules stack.
     *
     * Rules must be callables which return a boolean. If any of the rules return
     * boolean false current request will not be authenticated.
     */
    public function addRule(callable $callable): self
    {
        $new = clone $this;
        $new->rules = clone $this->rules;
        $new->rules->push($callable);
        return $new;
    }
}
