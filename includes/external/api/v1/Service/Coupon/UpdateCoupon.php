<?php

/**
 * /includes/external/api/v1/Service/Coupon/UpdateCoupon.php
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

#[OA\Put(
    path: '/api/v1/coupons/{Id}',
    tags: ['Coupon'],
    description: 'Update single coupon data by given Id',
    operationId: 'UpdateCoupon',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'coupons Id'
        )
    ],
    responses:[
        new OA\Response(
            response: 201,
            description: 'coupons data',
        ),
        new OA\Response(
            response: 400,
            description: 'invalid code supplied'
        ),
        new OA\Response(
            response: 500,
            description: 'coupons Id required'
        )
    ],
    security: [
        ['modified_auth' => ['UpdateCoupon']]
    ]
)]

final class UpdateCoupon extends BaseService
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

        $couponId = $args['id'];
        $data = (array)$request->getParsedBody();

        $result = $this->couponAction->UpdateCoupon($couponId, $data);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response, $result);
    }
}
