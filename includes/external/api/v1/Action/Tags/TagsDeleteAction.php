<?php

/**
 * /includes/external/api/v1/Action/Tags/TagsDeleteAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Tags;

use api\v1\Action\BaseAction;
use api\v1\Utility\LoggerHandler;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Service.
 */
trait TagsDeleteAction
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
                                          FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . "
                                         WHERE options_id = '" . (int)$optionId . "'");
        if (xtc_db_num_rows($option_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Option not found: %s', $optionId));
        } else {
            $products_query = xtc_db_query("SELECT *
                                                FROM " . TABLE_PRODUCTS_TAGS_VALUES . "
                                               WHERE options_id = '" . (int)$optionId . "'");
            $count = xtc_db_num_rows($products_query);
            if ($count > 0) {
                return $this->errormessage(sprintf('Option can not get deleted due to connected values: %s', $count), 400);
            } else {
                xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . "
                                      WHERE options_id = '" . (int)$optionId . "'");
            }
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
                                         FROM " . TABLE_PRODUCTS_TAGS_VALUES . "
                                        WHERE values_id = '" . (int)$valueId . "'");
        if (xtc_db_num_rows($value_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Value not found: %s', $valueId));
        } else {
            xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_TAGS_VALUES . "
                                  WHERE values_id = '" . (int)$valueId . "'");
        }
        return null;
    }
}
