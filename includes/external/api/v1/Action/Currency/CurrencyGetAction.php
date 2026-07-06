<?php

/**
 * /includes/external/api/v1/Action/Currency/CurrencyGetAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Currency;

use Exception;

/**
 * Service.
 */
trait CurrencyGetAction
{
    /**
     * Read a currency by the given currency id.
     *
     * @param int $currencyId The currency id
     *
     * @throws Exception
     *
     * @return array The currency data
     */
    public function GetSingleCurrency(int $currencyId): array
    {
        // Input validation
        if (empty($currencyId)) {
            throw new Exception('Currency ID required');
        }

        $currency_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CURRENCIES . "
                                           WHERE currencies_id = '" . (int)$currencyId . "'");
        if (xtc_db_num_rows($currency_query) < 1) {
            return $this->errormessage(sprintf('Currency not found: %s', $currencyId));
        } else {
            $currency = xtc_db_fetch_array($currency_query);
        }

        $result = $this->encode_request($currency);
        return $result;
    }

    /**
     * Read currencies by given conditions
     *
     * @param mixed[] $options
     *
     * @return array The currency data
     */
    public function GetCurrencies(array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM " . TABLE_CURRENCIES);
        $count = xtc_db_fetch_array($count_query);

        if ($count['total'] < 1) {
            return $this->errormessage('no Currency found');
        }

        $data = [];
        $currencies_query = xtc_db_query("SELECT currencies_id
                                              FROM " . TABLE_CURRENCIES . "
                                          ORDER BY currencies_id ASC
                                             LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($currencies = xtc_db_fetch_array($currencies_query)) {
            $data[] = $this->GetSingleCurrency($currencies['currencies_id']);
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
