<?php

/**
 * /includes/external/api/v1/Action/Tags/TagsGetAction.php
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
trait TagsGetAction
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
                                          FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . "
                                         WHERE options_id = '" . (int)$optionId . "'");
        if (xtc_db_num_rows($option_query) < 1) {
            return $this->errormessage(sprintf('Option not found: %s', $optionId));
        } else {
            $options_query = xtc_db_query("SELECT pto.*,
                                                    l.code
                                               FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . " pto
                                               JOIN " . TABLE_LANGUAGES . " l
                                                    ON l.languages_id = pto.languages_id
                                              WHERE pto.options_id = '" . (int)$optionId . "'");
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
                                         FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . "
                                     GROUP BY options_id");
        $count = [
            'total' => xtc_db_num_rows($count_query)
        ];

        if ($count['total'] < 1) {
            return $this->errormessage('no Options found');
        }

        $data = [];
        $options_query = xtc_db_query("SELECT options_id
                                           FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . "
                                       GROUP BY options_id 
                                       ORDER BY sort_order, options_id
                                          LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($options = xtc_db_fetch_array($options_query)) {
            $data[] = $this->GetSingleOption($options['options_id']);
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
                                         FROM " . TABLE_PRODUCTS_TAGS_VALUES . "
                                        WHERE values_id = '" . (int)$valueId . "'");
        if (xtc_db_num_rows($value_query) < 1) {
            return $this->errormessage(sprintf('Value not found: %s', $valueId));
        } else {
            $values_query = xtc_db_query("SELECT ptv.*,
                                                   l.code
                                              FROM " . TABLE_PRODUCTS_TAGS_VALUES . " ptv
                                              JOIN " . TABLE_LANGUAGES . " l
                                                   ON l.languages_id = ptv.languages_id
                                             WHERE ptv.values_id = '" . (int)$valueId . "'");
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

        $conditions = [];
        if (isset($this->options['option']) && preg_replace('/[^\d\,]/', '', $this->options['option']) != '') {
            $conditions[] = " options_id IN (" . preg_replace('/[^\d\,]/', '', $this->options['option']) . ") ";
        }

        $where = '';
        if (count($conditions) > 0) {
            $where = " WHERE " . implode(' AND ', $conditions);
        }

        $count_query = xtc_db_query("SELECT *
                                         FROM " . TABLE_PRODUCTS_TAGS_VALUES . "
                                              " . $where . "
                                     GROUP BY values_id");
        $count = [
            'total' => xtc_db_num_rows($count_query)
        ];

        if ($count['total'] < 1) {
            return $this->errormessage('no Values found');
        }

        $data = [];
        $values_query = xtc_db_query("SELECT values_id
                                          FROM " . TABLE_PRODUCTS_TAGS_VALUES . "
                                               " . $where . "
                                      GROUP BY values_id 
                                      ORDER BY sort_order, values_id
                                         LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($values = xtc_db_fetch_array($values_query)) {
            $data[] = $this->GetSingleValue($values['values_id']);
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
