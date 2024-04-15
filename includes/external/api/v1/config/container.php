<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  use Slim\App;
  use Slim\Factory\AppFactory;
  use Slim\Middleware\ErrorMiddleware;
  use Slim\Psr7\Factory\StreamFactory;
  use Slim\Interfaces\RouteParserInterface;
  use Selective\BasePath\BasePathMiddleware;
  use Selective\Validation\Encoder\JsonEncoder;
  use Selective\Validation\Middleware\ValidationExceptionMiddleware;
  use Selective\Validation\Transformer\ErrorDetailsResultTransformer;
  use Psr\Container\ContainerInterface;
  use Psr\Http\Message\ResponseFactoryInterface;
  use Tuupola\Middleware\HttpBasicAuthentication;
  use Tuupola\Middleware\JwtAuthentication;
  use api\v1\Auth\Authentication;
  use api\v1\Utility\LoggerHandler;
  use api\v1\Utility\ErrorHandler;
  
  return [
      'settings' => function () {
          return require __DIR__ . '/settings.php';
      },

      App::class => function (ContainerInterface $container) {
          AppFactory::setContainer($container);

          return AppFactory::create();
      },

      ResponseFactoryInterface::class => function (ContainerInterface $container) {
          return $container->get(App::class)->getResponseFactory();
      },

      StreamFactoryInterface::class => function () {
          return new StreamFactory();
      },

      RouteParserInterface::class => function (ContainerInterface $container) {
          return $container->get(App::class)->getRouteCollector()->getRouteParser();
      },

      LoggerHandler::class => function (ContainerInterface $container) {
          return new LoggerHandler($container->get('settings')['logger']);
      },

      ValidationExceptionMiddleware::class => function (ContainerInterface $container) {
          $factory = $container->get(ResponseFactoryInterface::class);

          return new ValidationExceptionMiddleware(
              $factory,
              new ErrorDetailsResultTransformer(),
              new JsonEncoder()
          );
      },

      ErrorMiddleware::class => function (ContainerInterface $container) {
          $settings = $container->get('settings')['error'];
          $app = $container->get(App::class);

          $logger = $container->get(LoggerHandler::class)->createLogger();

          $errorMiddleware = new ErrorMiddleware(
              $app->getCallableResolver(),
              $app->getResponseFactory(),
              (bool)$settings['display_error_details'],
              (bool)$settings['log_errors'],
              (bool)$settings['log_error_details'],
              $logger
          );

          $errorMiddleware->setDefaultErrorHandler($container->get(ErrorHandler::class));

          return $errorMiddleware;
      },

      BasePathMiddleware::class => function (ContainerInterface $container) {
          return new BasePathMiddleware($container->get(App::class));
      },

      Authentication::class => function (ContainerInterface $container) {
          return new Authentication([
              'error' => function ($response, $arguments) {              
                  $data = [
                    'error' => [
                      'message' => $arguments['message']
                    ]
                  ];
                  
                  $response->getBody()->write((string)json_encode($data));
                  return $response->withHeader('Content-Type', 'application/json');
              }
          ]);
      },

      JwtAuthentication::class => function (ContainerInterface $container) {
          if (!defined('MODULE_SYSTEM_MODIFIED_API_SECRET')
              || empty(MODULE_SYSTEM_MODIFIED_API_SECRET)
              )
          {
              throw new \RuntimeException("modified API not installed");
          }

          return new JwtAuthentication([
              'secret' => MODULE_SYSTEM_MODIFIED_API_SECRET,
              'error' => function ($response, $arguments) {              
                  $data = [
                    'error' => [
                      'message' => $arguments['message']
                    ]
                  ];
                  
                  $response->getBody()->write((string)json_encode($data));
                  return $response->withHeader('Content-Type', 'application/json');
              }
          ]);
      },
  ];
