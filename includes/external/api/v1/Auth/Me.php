<?php

/**
 * /includes/external/api/v1/Auth/Me.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/me',
    tags: ['Auth'],
    description: 'Get the identity of the customer the current access token belongs to, plus the '
        . 'set of API actions enabled for that account (Customers -> API Access in the shop backend), '
        . 'grouped by resource (Customer, Product, Order, ...). '
        . 'Lets a client discover who is logged in and which features to show, without guessing from '
        . '403 responses.',
    operationId: 'me',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Current customer identity and enabled API actions, grouped by resource'
        ),
        new OA\Response(
            response: 401,
            description: 'Missing/invalid access token, or the account no longer has API access'
        )
    ],
    security: [
        ['modified_auth' => []]
    ]
)]

final class Me
{
    /**
     * Identity columns selected from the customers table (kept out of the
     * permissions list below).
     *
     * @var string[]
     */
    private const IDENTITY_COLUMNS = [
        'customers_id',
        'customers_email_address',
        'customers_firstname',
        'customers_lastname',
    ];

    /**
     * Human-readable label per resource group id.
     *
     * These ids are the values written into the pseudo-customer row
     * `api_access.customers_id = 'groups'` by the API Access module
     * (admin/includes/modules/system/api_access.php, update()); they group the
     * per-action permission columns by the resource they belong to. Kept in
     * sync with that module's group id assignments.
     *
     * @var array<int,string>
     */
    private const GROUP_LABELS = [
        10 => 'Customer',
        20 => 'Category',
        30 => 'Product',
        31 => 'Manufacturer',
        32 => 'Attributes',
        33 => 'Tags',
        40 => 'Order',
        50 => 'Country',
        60 => 'Shipping',
        70 => 'Campaign',
        80 => 'Currency',
        90 => 'Language',
        100 => 'Newsletter',
        110 => 'Configuration',
        120 => 'Content',
        130 => 'Coupon',
        140 => 'Schema',
    ];

    /**
     * Label used for a permission column with no (or an unmapped) group id.
     */
    private const UNGROUPED_LABEL = 'Other';

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
        $token = $request->getAttribute('token');
        $email = is_array($token) ? (string)($token['sub'] ?? '') : '';

        /* Re-resolve identity + access flags fresh from the database on every */
        /* call, rather than trusting the JWT payload, so a revoked account or */
        /* access change takes effect immediately (within the token's 10 minute */
        /* lifetime) instead of only after the next login. */
        $query = xtc_db_query("SELECT c.customers_id,
                                       c.customers_email_address,
                                       c.customers_firstname,
                                       c.customers_lastname,
                                       aa.*
                                  FROM " . TABLE_CUSTOMERS . " c
                                  JOIN `api_access` aa
                                       ON aa.customers_id = c.customers_id
                                 WHERE c.customers_email_address = '" . xtc_db_input($email) . "'
                                 LIMIT 1");
        $row = xtc_db_fetch_array($query);

        if (!is_array($row)) {
            return $this->unauthorized($response, 'Invalid access token or API access revoked');
        }

        /* The pseudo-customer row `customers_id = 'groups'` has no matching row */
        /* in the customers table, so it needs its own (non-joined) lookup. Its */
        /* column values are resource group ids rather than 0/1 access flags. */
        $group_query = xtc_db_query("SELECT *
                                         FROM `api_access`
                                        WHERE customers_id = 'groups'
                                        LIMIT 1");
        $group_row = xtc_db_fetch_array($group_query);
        $group_row = is_array($group_row) ? $group_row : [];

        $permissions = [];
        foreach ($row as $column => $value) {
            if (in_array($column, self::IDENTITY_COLUMNS, true)) {
                continue;
            }
            if ((int)$value === 0) {
                continue;
            }

            $groupId = isset($group_row[$column]) ? (int)$group_row[$column] : 0;
            $label = self::GROUP_LABELS[$groupId] ?? self::UNGROUPED_LABEL;

            $permissions[$label][] = $column;
        }

        foreach ($permissions as &$actions) {
            sort($actions);
        }
        unset($actions);
        ksort($permissions);

        $data = [
            'customers_id' => (int)$row['customers_id'],
            'email' => (string)$row['customers_email_address'],
            'firstname' => (string)$row['customers_firstname'],
            'lastname' => (string)$row['customers_lastname'],
            /* Force object encoding even when empty ({} not []), so clients can */
            /* rely on "permissions" always decoding as a keyed group map. */
            'permissions' => $permissions === [] ? new \stdClass() : $permissions,
        ];

        $response->getBody()->write((string)json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Build a 401 JSON error response matching the API error format.
     *
     * @param ResponseInterface $response The response
     * @param string $message The error message
     *
     * @return ResponseInterface The response
     */
    private function unauthorized(ResponseInterface $response, string $message): ResponseInterface
    {
        $data = [
            'error' => [
                'message' => $message,
            ],
        ];

        $response->getBody()->write((string)json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}
