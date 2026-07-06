<?php

/**
 * /includes/external/api/v1/Utility/ErrorHandler.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Utility;

use DomainException;
use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Throwable;

/**
 * Default Error Renderer.
 */
final class ErrorHandler
{
    /**
     * @var Responder
     */
    private $responder;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param LoggerHandler $LoggerHandler The logger factory
     */
    public function __construct(
        Responder $responder,
        ResponseFactoryInterface $responseFactory,
        LoggerHandler $LoggerHandler
    ) {
        $this->responder = $responder;
        $this->responseFactory = $responseFactory;
        $this->logger = $LoggerHandler->createLogger();
    }

    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request The request
     * @param Throwable $exception The exception
     * @param bool $displayErrorDetails Show error details
     * @param bool $logErrors Log errors
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors
    ): ResponseInterface {
        // Log error
        if ($logErrors) {
            $exceptions = $this->getExceptionText($exception);
            $this->logger->error(
                sprintf(
                    'Exception found for URL: %s',
                    $request->getUri()->getPath()
                )
            );
            $this->logger->error(
                sprintf(
                    '%s, Method: %s',
                    array_shift($exceptions),
                    $request->getMethod()
                )
            );

            foreach ($exceptions as $error) {
                $this->logger->error($error);
            }
        }

        // Detect status code
        $statusCode = $this->getHttpStatusCode($exception);

        // Error message
        $errorMessage = $this->getErrorMessage($exception, $statusCode, $displayErrorDetails);
        $Message = $exception->getMessage();

        // The API's own code always throws \Exception (or a subclass) for
        // deliberate, client-safe validation/business errors (e.g. "Product not
        // found", "Access for X required") - those messages are meant to reach
        // the client. A \Error (TypeError, ArgumentCountError, ...) signals an
        // unexpected bug instead, and may carry internal details (argument
        // values, internal state), so never forward its message to the client.
        if ($exception instanceof \Error) {
            $Message = '';
        }

        // Render response
        $response = $this->responseFactory->createResponse();
        $response = $this->responder->withJson($response, [
            'error' => [
                'message' => $Message ?: $errorMessage,
            ],
        ]);

        return $response->withStatus($statusCode);
    }

    /**
     * Get http status code.
     *
     * @param Throwable $exception The exception
     *
     * @return int The http code
     */
    private function getHttpStatusCode(Throwable $exception): int
    {
        // Detect status code
        $statusCode = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;

        if ($exception instanceof HttpException) {
            $statusCode = (int)$exception->getCode();
        }

        if ($exception instanceof DomainException || $exception instanceof InvalidArgumentException) {
            // Bad request
            $statusCode = StatusCodeInterface::STATUS_BAD_REQUEST;
        }

        $file = basename($exception->getFile());
        if ($file === 'CallableResolver.php') {
            $statusCode = StatusCodeInterface::STATUS_NOT_FOUND;
        }

        return $statusCode;
    }

    /**
     * Get error message.
     *
     * @param Throwable $exception The error
     * @param int $statusCode The http status code
     * @param bool $displayErrorDetails Display details
     *
     * @return string The message
     */
    private function getErrorMessage(Throwable $exception, int $statusCode, bool $displayErrorDetails): string
    {
        $reasonPhrase = $this->responseFactory->createResponse()->withStatus($statusCode)->getReasonPhrase();
        $errorMessage = sprintf('%s %s', $statusCode, $reasonPhrase);

        if ($displayErrorDetails === true) {
            $errorMessage = sprintf(
                '%s - Error details: %s',
                $errorMessage,
                implode("\n", $this->getExceptionText($exception))
            );
        }

        return $errorMessage;
    }

    /**
     * Get exception text.
     *
     * @param Throwable $exception Error
     * @param int $maxLength The max length of the error message
     *
     * @return array The full error message
     */
    private function getExceptionText(Throwable $exception, int $maxLength = 0): array
    {
        $code = $exception->getCode();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $message = $exception->getMessage();
        $trace = $exception->getTraceAsString();
        $trace_array = explode('#', $trace);
        $trace_array = array_filter($trace_array);

        $error = [];
        $error[] = sprintf('[%s] %s in %s on line %s.', $code, $message, $file, $line);
        if (count($trace_array) > 0) {
            foreach ($trace_array as $trace) {
                $error[] = 'Backtrace #' . $trace;
            }
        }

        return $error;
    }
}
