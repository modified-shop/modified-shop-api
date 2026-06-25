<?php

/**
 * /includes/external/api/v1/Service/Customer/DeleteMemo.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Service\Customer;

use api\v1\Service\BaseService;
use api\v1\Action\Customer\CustomerAction;
use api\v1\Utility\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/v1/customers/{Id}/memo/{mId}',
    tags: ['Customer'],
    description: 'Delete single customer memo by given Id',
    operationId: 'DeleteMemo',
    parameters: [
        new OA\Parameter(
            name: 'Id',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'customer Id'
        ),
        new OA\Parameter(
            name: 'mId',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
            ),
            description: 'customer memo Id'
        )
    ],
    responses:[
        new OA\Response(
            response: 204,
            description: 'no data',
        ),
        new OA\Response(
            response: 403,
            description: 'customer not found'
        ),
        new OA\Response(
            response: 403,
            description: 'customer memo not found'
        ),
        new OA\Response(
            response: 500,
            description: 'customer Id required'
        ),
        new OA\Response(
            response: 500,
            description: 'customer memo Id required'
        )
    ],
    security: [
        ['modified_auth' => ['DeleteMemo']]
    ]
)]

final class DeleteMemo extends BaseService
{
    /**
     * @var CustomerAction
     */
    private $customerAction;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param CustomerAction $customerAction The customer reader
     * @param Responder $responder The responder
     */
    public function __construct(CustomerAction $customerAction, Responder $responder)
    {
        $this->customerAction = $customerAction;
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

        $customerId = (int)$args['id'];
        $memoId = (int)$args['mid'];

        // Input validation
        if (empty($memoId)) {
            throw new Exception('Memo ID required');
        }

        $result = $this->customerAction->DeleteMemo($customerId, $memoId);

        if (isset($result['errormessage'])) {
            return $this->responder->withJson($response, $result['errormessage'])->withStatus($result['code']);
        }
        return $this->responder->withJson($response)->withStatus(204);
    }
}
