<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Auth;

  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;

  use Firebase\JWT\JWT;
  use Tuupola\Base62;

  /**
   * Action
   */
  final class JwtAuth
  {
      /**
       * The constructor.
       */
      public function __construct()
      {
      }

      /**
       * Invoke.
       *
       * @param ServerRequestInterface $request The request
       * @param ResponseInterface $response The response
       * @param array<mixed> $args The route arguments
       *
       * @return ResponseInterface The response
       */
      public function __invoke(
          ServerRequestInterface $request,
          ResponseInterface $response,
          array $args = []
      ): ResponseInterface {
          if (!defined('MODULE_SYSTEM_MODIFIED_API_SECRET')
              || empty(MODULE_SYSTEM_MODIFIED_API_SECRET)
              )
          {
              throw new \RuntimeException("modified API not installed");
          }
          
          $now = new \DateTime();
          $future = new \DateTime('+10 minutes');
          $server = $request->getServerParams();
          $jti = (new Base62)->encode(random_bytes(16));
          $payload = [
              'iat' => $now->getTimeStamp(),
              'exp' => $future->getTimeStamp(),
              'jti' => $jti,
              'sub' => $server['PHP_AUTH_USER']
          ];
          $secret = MODULE_SYSTEM_MODIFIED_API_SECRET;
          $token = JWT::encode($payload, $secret, 'HS256');
          $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires' => $future->getTimeStamp(),
          ];

          // Build the HTTP response
          $response->getBody()->write((string)json_encode($data));

          return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
      }
  }
