<?php

/**
 * /includes/external/api/v1/config/meta.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

use OpenApi\Generator as OpenApiGenerator;

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

    $spec['tags'][] = ['name' => 'Meta', 'description' => 'API version information'];

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
