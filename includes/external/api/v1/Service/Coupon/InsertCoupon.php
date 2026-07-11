<?php

/**
 * /includes/external/api/v1/Service/Coupon/InsertCoupon.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Coupon;

use api\v1\Service\BaseService;
use api\v1\Action\Coupon\CouponAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/coupons',
    tags: ['Coupon'],
    summary: 'Insert single coupon data',
    description: 'Insert single coupon data',
    operationId: 'InsertCoupon',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'coupons',
                        type: 'object',
                        description: 'Columns of the coupons database table. The exact set is '
                            . 'installation-specific; discover it via GET /api/v1/schema/coupons.'
                    ),
                    new OA\Property(
                        property: 'coupons_description',
                        type: 'object',
                        description: 'Per-language coupon text, keyed by language code (e.g. "de", "en"). Each '
                            . 'value is an object of coupons_description columns. Discover the exact columns '
                            . 'via GET /api/v1/schema/coupons_description.'
                    )
                ]
            )
        )
    ),
    responses:[
        new OA\Response(
            response: 201,
            description: 'coupons data',
        ),
        new OA\Response(
            response: 400,
            description: 'invalid code supplied'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class InsertCoupon extends BaseService
{
    /**
     * @var CouponAction
     */
    private $couponAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param CouponAction $couponAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(CouponAction $couponAction, Responder $responder)
    {
        $this->couponAction = $couponAction;
        $this->responder = $responder;
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
        array $args
    ): ResponseInterface {
        $this->CheckAccess($request, $response);

        $data = (array)$request->getParsedBody();

        $result = $this->couponAction->InsertCoupon($data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result)->withStatus(201);
    }
}
