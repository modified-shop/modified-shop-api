<?php

/**
 * /includes/external/api/v1/Action/Attributes/AttributesDeleteAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Attributes;

use api\v1\Action\BaseAction;
use api\v1\Utility\LoggerHandler;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Service.
 */
trait AttributesDeleteAction
{
    /**
     * Delete a option by the given option id.
     *
     * @param int $optionId The option id
     *
     * @throws Exception
     *
     * @return array<mixed>|null
     */
    public function DeleteOption(int $optionId): ?array
    {
        // Input validation
        if (empty($optionId)) {
            throw new Exception('Option ID required');
        }

        $option_query = xtc_db_query("SELECT *
                                          FROM " . TABLE_PRODUCTS_OPTIONS . "
                                         WHERE products_options_id = '" . (int)$optionId . "'");
        if (xtc_db_num_rows($option_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Option not found: %s', $optionId));
        } else {
            xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS . "
                                  WHERE products_options_id = '" . (int)$optionId . "'");

            xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                  WHERE products_options_id = '" . (int)$optionId . "'");
        }
        return null;
    }

    /**
     * Delete a value by the given value id.
     *
     * @param int $valueId The value id
     *
     * @throws Exception
     *
     * @return array<mixed>|null
     */
    public function DeleteValue(int $valueId): ?array
    {
        // Input validation
        if (empty($valueId)) {
            throw new Exception('Value ID required');
        }

        $value_query = xtc_db_query("SELECT *
                                         FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                        WHERE products_options_values_id = '" . (int)$valueId . "'");
        if (xtc_db_num_rows($value_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Option value not found: %s', $valueId));
        } else {
            xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                  WHERE products_options_values_id = '" . (int)$valueId . "'");

            xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                  WHERE products_options_values_id = '" . (int)$valueId . "'");
        }
        return null;
    }

    /**
     * Delete a value from options by the given option id and value id.
     *
     * @param int $optionId The option id
     * @param int $valueId The value id
     *
     * @throws Exception
     *
     * @return array<mixed>|null
     */
    public function DeleteAttributes(int $optionId, int $valueId): ?array
    {
        // Input validation
        if (empty($optionId)) {
            throw new Exception('Option ID required');
        }

        $where = '';
        if ($valueId > 0) {
            $where = "AND products_options_values_id = '" . (int)$valueId . "'";
        }

        $attributes_query = xtc_db_query("SELECT *
                                              FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                             WHERE products_options_id = '" . (int)$optionId . "'
                                                   " . $where);
        if (xtc_db_num_rows($attributes_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Option value not found: %s', $optionId));
        } else {
            while ($attributes = xtc_db_fetch_array($attributes_query)) {
                xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                      WHERE products_options_id = '" . (int)$optionId . "'
                                        AND products_options_values_id = '" . (int)$attributes['products_options_values_id'] . "'");
            }
        }
        return null;
    }

    /**
     * Delete all values from options by the given option id.
     *
     * @param int $optionId The option id
     *
     * @throws Exception
     *
     * @return array<mixed>|null
     */
    public function DeleteAllAttributes(int $optionId): ?array
    {
        // Input validation
        if (empty($optionId)) {
            throw new Exception('Option ID required');
        }

        return $this->DeleteAttributes($optionId, 0);
    }
}
