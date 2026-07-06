<?php

/**
 * /includes/external/api/v1/Action/Attributes/AttributesGetAction.php
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

use Exception;

/**
 * Service.
 */
trait AttributesGetAction
{
    /**
     * Read an option by the given option id.
     *
     * @param int $optionId The option id
     *
     * @throws Exception
     *
     * @return array The option data
     */
    public function GetSingleOption(int $optionId): array
    {
        // Input validation
        if (empty($optionId)) {
            throw new Exception('Option ID required');
        }

        $option = [];
        $option_query = xtc_db_query("SELECT *
                                          FROM " . TABLE_PRODUCTS_OPTIONS . "
                                         WHERE products_options_id = '" . (int)$optionId . "'");
        if (xtc_db_num_rows($option_query) < 1) {
            return $this->errormessage(sprintf('Option not found: %s', $optionId));
        } else {
            $options_query = xtc_db_query("SELECT po.*,
                                                    l.code
                                               FROM " . TABLE_PRODUCTS_OPTIONS . " po
                                               JOIN " . TABLE_LANGUAGES . " l
                                                    ON l.languages_id = po.language_id
                                              WHERE po.products_options_id = '" . (int)$optionId . "'");
            while ($options = xtc_db_fetch_array($options_query)) {
                $code = $options['code'];
                unset($options['code']);

                $option[$code] = $options;
            }
        }

        $result = $this->encode_request($option);
        return $result;
    }

    /**
     * Read options by given conditions
     *
     * @param mixed[] $options
     *
     * @return array The options data
     */
    public function GetOptions(array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        $count_query = xtc_db_query("SELECT *
                                         FROM " . TABLE_PRODUCTS_OPTIONS . "
                                     GROUP BY products_options_id");
        $count = [
            'total' => xtc_db_num_rows($count_query)
        ];

        if ($count['total'] < 1) {
            return $this->errormessage('no Options found');
        }

        $data = [];
        $options_query = xtc_db_query("SELECT products_options_id
                                           FROM " . TABLE_PRODUCTS_OPTIONS . "
                                       GROUP BY products_options_id 
                                       ORDER BY products_options_sortorder, products_options_id
                                          LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($options = xtc_db_fetch_array($options_query)) {
            $data[] = $this->GetSingleOption($options['products_options_id']);
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
     * Read an value by the given value id.
     *
     * @param int $valueId The value id
     *
     * @throws Exception
     *
     * @return array The value data
     */
    public function GetSingleValue(int $valueId): array
    {
        // Input validation
        if (empty($valueId)) {
            throw new Exception('Value ID required');
        }

        $value = [];
        $value_query = xtc_db_query("SELECT *
                                         FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                        WHERE products_options_values_id = '" . (int)$valueId . "'");
        if (xtc_db_num_rows($value_query) < 1) {
            return $this->errormessage(sprintf('Value not found: %s', $valueId));
        } else {
            $values_query = xtc_db_query("SELECT pov.*,
                                                   l.code
                                              FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                              JOIN " . TABLE_LANGUAGES . " l
                                                   ON l.languages_id = pov.language_id
                                             WHERE pov.products_options_values_id = '" . (int)$valueId . "'");
            while ($values = xtc_db_fetch_array($values_query)) {
                $code = $values['code'];
                unset($values['code']);

                $value[$code] = $values;
            }
        }

        $result = $this->encode_request($value);
        return $result;
    }

    /**
     * Read value by given conditions
     *
     * @param mixed[] $options
     *
     * @return array The value data
     */
    public function GetValues(array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        $count_query = xtc_db_query("SELECT *
                                         FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                     GROUP BY products_options_values_id");
        $count = [
            'total' => xtc_db_num_rows($count_query)
        ];

        if ($count['total'] < 1) {
            return $this->errormessage('no Values found');
        }

        $data = [];
        $values_query = xtc_db_query("SELECT products_options_values_id
                                          FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                      GROUP BY products_options_values_id 
                                      ORDER BY products_options_values_sortorder, products_options_values_id
                                         LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($values = xtc_db_fetch_array($values_query)) {
            $data[] = $this->GetSingleValue($values['products_options_values_id']);
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
     * Read all values by the given option id.
     *
     * @param int $optionId The option id
     * @param mixed[] $options
     *
     * @return array The attributes data
     */
    public function GetAttributes(int $optionId, array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                        WHERE products_options_id = '" . (int)$optionId . "'");
        $count = xtc_db_fetch_array($count_query);

        if ($count['total'] < 1) {
            return $this->errormessage('no Attributes found');
        }

        $data = [];
        $values_query = xtc_db_query("SELECT pov2po.*
                                          FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po
                                          JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                               ON pov2po.products_options_values_id  = pov.products_options_values_id 
                                         WHERE pov2po.products_options_id = '" . (int)$optionId . "'
                                      GROUP BY pov2po.products_options_values_id 
                                      ORDER BY pov.products_options_values_sortorder, pov2po.products_options_values_id
                                         LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($values = xtc_db_fetch_array($values_query)) {
            $data[] = array_merge($values, $this->GetSingleValue($values['products_options_values_id']));
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
}
