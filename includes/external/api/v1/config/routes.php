<?php

/**
 * /includes/external/api/v1/config/routes.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Tuupola\Middleware\HttpBasicAuthentication;
use Tuupola\Middleware\JwtAuthentication;
use api\v1\Auth\Authentication;
use OpenApi\Generator as OpenApiGenerator;
use Symfony\Component\Finder\Finder;

return function (App $app) {
    $settings = $app->getContainer()->get('settings');

    // oauth
    $app->post('/v1/oauth', \api\v1\Auth\JwtAuth::class)->add(Authentication::class);

    // oauth token refresh (self-validating: no credentials / access token required)
    $app->post('/v1/oauth/refresh', \api\v1\Auth\RefreshToken::class);

    // oauth logout (revoke a refresh token; proof of possession is the token itself)
    $app->post('/v1/oauth/logout', \api\v1\Auth\Logout::class);

    // docs
    $app->get('/v1/swagger.json', function ($request, $response, $args) use ($settings) {
        $swagger = (new OpenApiGenerator())->generate([
            DIR_FS_EXTERNAL . 'api/v1/Service/',
            DIR_FS_EXTERNAL . 'api/v1/Auth/',
        ]);

        $prefix = (string)preg_replace(
            '#/api/v1/swagger\.json$#',
            '',
            $request->getUri()->getPath()
        );

        $spec = json_decode((string)json_encode($swagger), true);
        $spec['info']['version'] = $settings['version'];

        $spec['paths']['/api/v1/version']['get'] = [
            'tags' => ['Meta'],
            'description' => 'Get the API version and the minimum required shop version.',
            'operationId' => 'version',
            'security' => [],
            'responses' => [
                '200' => [
                    'description' => 'Version information',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'version' => ['type' => 'string'],
                                    'requires' => ['type' => 'string'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $spec['servers'] = [['url' => $prefix === '' ? '/' : $prefix]];
        if (isset($spec['components']['securitySchemes']['modified_auth']['flows']['password'])) {
            $spec['components']['securitySchemes']['modified_auth']['flows']['password'] = [
                'tokenUrl' => $prefix . '/api/v1/oauth',
                'scopes' => new \stdClass(),
            ];
        }

        $response->getBody()->write((string)json_encode($spec));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // version
    $app->get('/v1/version', function ($request, $response, $args) use ($settings) {
        $data = [
            'version' => $settings['version'],
            'requires' => $settings['min_shop_version'],
        ];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // routes
    $app->group(
        '/v1',
        function (RouteCollectorProxy $app) {
            // customers
            include __DIR__ . '/customers.php';

            // categories
            include __DIR__ . '/categories.php';

            // products
            include __DIR__ . '/products.php';

            // manufacturers
            include __DIR__ . '/manufacturers.php';

            // attributes
            include __DIR__ . '/attributes.php';

            // tags
            include __DIR__ . '/tags.php';

            // orders
            include __DIR__ . '/orders.php';

            // countries
            include __DIR__ . '/countries.php';

            // shipping
            include __DIR__ . '/shipping.php';

            // contents
            include __DIR__ . '/contents.php';

            // campaigns
            include __DIR__ . '/campaigns.php';

            // currencies
            include __DIR__ . '/currencies.php';

            // languages
            include __DIR__ . '/languages.php';

            // newsletters
            include __DIR__ . '/newsletters.php';

            // configurations
            include __DIR__ . '/configurations.php';

            // coupons
            include __DIR__ . '/coupons.php';

            // dhl
            include __DIR__ . '/dhl.php';

            // schema
            include __DIR__ . '/schema.php';
        }
    )->add(JwtAuthentication::class);
};
