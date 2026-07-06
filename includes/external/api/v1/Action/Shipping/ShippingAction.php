<?php

/**
 * /includes/external/api/v1/Action/Shipping/ShippingAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Shipping;

use api\v1\Action\BaseAction;
use Exception;

/**
 * Service.
 */
final class ShippingAction extends BaseAction
{
    use ShippingGetAction;
    use ShippingDeleteAction;

    /**
     * Insert a carrier by the given options.
     *
     * @param mixed[] $options
     *
     * @return array The carrier data
     */
    public function InsertCarrier(array $options): array
    {
        $carrier = $this->InsertUpdateCarrier(0, $options);

        return $carrier;
    }

    /**
     * Insert or Update a carrier by the given carrier id.
     *
     * @param int $carrierId The carrier id
     * @param mixed[] $options
     *
     * @throws Exception
     *
     * @return array The carrier data
     */
    public function InsertUpdateCarrier(int $carrierId, array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        if ($carrierId > 0) {
            $action = 'update';
            $carrier_query = xtc_db_query("SELECT *
                                               FROM " . TABLE_CARRIERS . "
                                              WHERE carrier_id = '" . (int)$carrierId . "'");
            if (xtc_db_num_rows($carrier_query) < 1) {
                return $this->errormessage(sprintf('Carrier not found: %s', $carrierId));
            } else {
                $carrier = xtc_db_fetch_array($carrier_query);
            }
        } else {
            $action = 'insert';
            $carrier = $this->getDefaultTableValues(TABLE_CARRIERS);
        }

        foreach ($carrier as $key => $value) {
            if (isset($this->options[$key])) {
                $carrier[$key] = $this->options[$key];
            }
        }

        if ($action == 'insert') {
            $check_query = xtc_db_query("SELECT *
                                             FROM " . TABLE_CARRIERS . "
                                            WHERE carrier_name = '" . xtc_db_input($carrier['carrier_name']) . "'");
            if (xtc_db_num_rows($check_query) > 0) {
                return $this->errormessage('Carrier Name already exists', 400);
            }
        }

        // Input validation
        $this->checkTableData(TABLE_CARRIERS, $carrier);
        unset($carrier['carrier_id']);

        xtc_db_perform(TABLE_CARRIERS, $carrier, $action, "carrier_id = '" . (int)$carrierId . "'");
        if ($action == 'insert') {
            $carrierId = xtc_db_insert_id();
        }

        return $this->GetSingleCarrier($carrierId);
    }

    /**
     * Insert a shipping status by the given options.
     *
     * @param mixed[] $options
     *
     * @return array The shipping status data
     */
    public function InsertShippingStatus(array $options): array
    {
        $shipping_status = $this->InsertUpdateShippingStatus(0, $options);

        return $shipping_status;
    }

    /**
     * Insert or Update a shipping status by given shipping status id.
     *
     * @param int $shippingStatusId The shipping status id
     * @param mixed[] $options
     *
     * @return array The shipping status data
     */
    public function InsertUpdateShippingStatus(int $shippingStatusId, array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        $languages_query = xtc_db_query("SELECT *
                                             FROM " . TABLE_LANGUAGES);
        while ($languages = xtc_db_fetch_array($languages_query)) {
            $where = '';
            if ($shippingStatusId > 0) {
                $where = "AND shipping_status_id = '" . (int)$shippingStatusId . "'";
                $shipping_status_query = xtc_db_query("SELECT *
                                                           FROM " . TABLE_SHIPPING_STATUS . "
                                                          WHERE language_id = '" . (int)$languages['languages_id'] . "'
                                                                " . $where);
                if (xtc_db_num_rows($shipping_status_query) < 1) {
                    $action = 'insert';
                    $shipping_status = $this->getDefaultTableValues(TABLE_SHIPPING_STATUS);
                    $shipping_status['shipping_status_id'] = $shippingStatusId;
                    $shipping_status['language_id'] = (int)$languages['languages_id'];
                } else {
                    $action = 'update';
                    $shipping_status = xtc_db_fetch_array($shipping_status_query);
                }
            } else {
                $action = 'insert';
                $shipping_status = $this->getDefaultTableValues(TABLE_SHIPPING_STATUS);

                if ($shippingStatusId < 1) {
                    $next_id_query = xtc_db_query("SELECT max(shipping_status_id) as shipping_status_id 
                                                       FROM " . TABLE_SHIPPING_STATUS . "");
                    $next_id = xtc_db_fetch_array($next_id_query);
                    $shippingStatusId = $next_id['shipping_status_id'] + 1;
                }

                $shipping_status['shipping_status_id'] = $shippingStatusId;
                $shipping_status['language_id'] = (int)$languages['languages_id'];
            }

            foreach ($shipping_status as $key => $value) {
                if (isset($this->options[$languages['code']][$key])) {
                    $shipping_status[$key] = $this->options[$languages['code']][$key];
                }
            }

            // Input validation
            $this->checkTableData(TABLE_SHIPPING_STATUS, $shipping_status);
            xtc_db_perform(TABLE_SHIPPING_STATUS, $shipping_status, $action, "language_id = '" . (int)$languages['languages_id'] . "' " . $where);
        }

        return $this->GetSingleShippingStatus($shippingStatusId);
    }
}
