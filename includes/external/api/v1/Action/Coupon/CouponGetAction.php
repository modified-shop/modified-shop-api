<?php

/**
 * /includes/external/api/v1/Action/Coupon/CouponGetAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Coupon;

use Exception;

/**
 * Service.
 */
trait CouponGetAction
{
    /**
     * Read a coupon by the given coupon id.
     *
     * @param int $couponId The coupon id
     *
     * @throws Exception
     *
     * @return array The coupon data
     */
    public function GetCouponDetails(int $couponId): array
    {
        // Input validation
        if (empty($couponId)) {
            throw new Exception('Coupon ID required');
        }

        $coupon_query = xtc_db_query("SELECT *
                                          FROM " . TABLE_COUPONS . "
                                         WHERE coupon_id = '" . (int)$couponId . "'");
        if (xtc_db_num_rows($coupon_query) < 1) {
            return $this->errormessage(sprintf('Coupon not found: %s', $couponId));
        } else {
            // disable Exception
            $this->throw_exception = false;

            $result = [
                'coupons' => $this->GetCoupon($couponId),
                'coupons_description' => $this->GetCouponDescription($couponId),
            ];

            return $result;
        }
    }

    /**
     * Read coupons by given conditions
     *
     * @param mixed[] $options
     *
     * @return array The coupon data
     */
    public function GetCoupons(array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        $conditions = [];
        if (isset($this->options['status']) && preg_replace('/[^\w\,]/', '', $this->options['status']) != '') {
            $data = preg_replace('/[^\w\,]/', '', $this->options['status']);
            $conditions[] = " coupon_active IN ('" . str_replace(',', "','", $data) . "') ";
        }
        if (isset($this->options['type']) && preg_replace('/[^\w\,]/', '', $this->options['type']) != '') {
            $data = preg_replace('/[^\w\,]/', '', $this->options['type']);
            $conditions[] = " coupon_type IN ('" . str_replace(',', "','", $data) . "') ";
        }
        if (isset($this->options['validfrom']) && (int)$this->options['validfrom'] > 0) {
            $conditions[] = " coupon_start_date >= '" . date('Y-m-d H:i:s', (int)$this->options['validfrom']) . "' ";
        }
        if (isset($this->options['validto']) && (int)$this->options['validto'] > 0) {
            $conditions[] = " coupon_expire_date <= '" . date('Y-m-d H:i:s', (int)$this->options['validto']) . "' ";
        }
        if ((int)$this->options['from'] > 0) {
            $conditions[] = " date_added >= '" . date('Y-m-d H:i:s', (int)$this->options['from']) . "' ";
        }
        if ((int)$this->options['to'] > 0) {
            $conditions[] = " date_added <= '" . date('Y-m-d H:i:s', (int)$this->options['to']) . "' ";
        }

        $where = '';
        if (count($conditions) > 0) {
            $where = " WHERE " . implode(' AND ', $conditions);
        }

        $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM " . TABLE_COUPONS . "
                                              " . $where);
        $count = xtc_db_fetch_array($count_query);

        if ($count['total'] < 1) {
            return $this->errormessage('no Coupon found');
        }

        $data = [];
        $coupon_query = xtc_db_query("SELECT coupon_id
                                          FROM " . TABLE_COUPONS . "
                                               " . $where . "
                                      ORDER BY coupon_id ASC
                                         LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($coupon = xtc_db_fetch_array($coupon_query)) {
            $data[] = $this->GetCouponDetails($coupon['coupon_id']);
        }

        $result = [
            'paging' => [
                'total' => $count['total']
            ],
            'data' => $data
        ];

        if ($count['total'] > count($data)) {
            if ($this->options['page'] > 1) {
                $result['paging']['prev'] = HTTPS_SERVER . DIR_WS_CATALOG . ltrim($this->options['path'], '/') . '?' . xtc_get_all_get_params(array('page')) . 'page=' . ($this->options['page'] - 1);
            }
            if (((($this->options['page'] - 1) * $this->options['limit']) + $this->options['limit']) < $count['total']) {
                $result['paging']['next'] = HTTPS_SERVER . DIR_WS_CATALOG . ltrim($this->options['path'], '/') . '?' . xtc_get_all_get_params(array('page')) . 'page=' . ($this->options['page'] + 1);
            }
        }

        return $result;
    }

    /**
     * Read a coupon by the given coupon id.
     *
     * @param int $couponId The coupon id
     *
     * @throws Exception
     *
     * @return array The coupon data
     */
    public function GetSingleCoupon(int $couponId, array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        // Input validation
        if (empty($couponId)) {
            throw new Exception('Coupon ID required');
        }

        $result = $this->GetCouponDetails($couponId);
        return $result;
    }

    /**
     * Read a coupon by the given coupon id.
     *
     * @param int $couponId The coupon id
     *
     * @throws Exception
     *
     * @return array The coupon data
     */
    public function GetCoupon(int $couponId): array
    {
        // Input validation
        if (empty($couponId)) {
            throw new Exception('Coupon ID required');
        }

        $coupon_query = xtc_db_query("SELECT *
                                          FROM " . TABLE_COUPONS . "
                                         WHERE coupon_id = '" . (int)$couponId . "'");
        if (xtc_db_num_rows($coupon_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Coupon not found: %s', $couponId));
        } else {
            $coupon = xtc_db_fetch_array($coupon_query);
        }

        $result = $this->encode_request($coupon);
        return $result;
    }

    /**
     * Read a coupon description by the given coupon id.
     *
     * @param int $couponId The coupon id
     *
     * @throws Exception
     *
     * @return array The coupon data
     */
    public function GetCouponDescription(int $couponId): array
    {
        // Input validation
        if (empty($couponId)) {
            throw new Exception('Coupon ID required');
        }

        $coupon_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_COUPONS_DESCRIPTION . "
                                           WHERE coupon_id = '" . (int)$couponId . "'");
        if (xtc_db_num_rows($coupon_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Coupon description not found: %s', $couponId));
        } else {
            $description = [];
            $coupon_description_query = xtc_db_query("SELECT cd.*,
                                                               l.code
                                                          FROM " . TABLE_COUPONS_DESCRIPTION . " cd
                                                          JOIN " . TABLE_LANGUAGES . " l
                                                               ON l.languages_id = cd.language_id
                                                         WHERE cd.coupon_id = '" . (int)$couponId . "'");
            while ($coupon_description = xtc_db_fetch_array($coupon_description_query)) {
                $code = $coupon_description['code'];
                unset($coupon_description['code']);

                $description[$code] = $coupon_description;
            }
        }

        $result = $this->encode_request($description);
        return $result;
    }
}
