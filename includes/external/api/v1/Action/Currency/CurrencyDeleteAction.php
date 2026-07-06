<?php

/**
 * /includes/external/api/v1/Action/Currency/CurrencyDeleteAction.php
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
trait CurrencyDeleteAction
{
    /**
     * Delete a currency by the given currency id.
     *
     * @param int $currencyId The currency id
     *
     * @throws Exception
     *
     * @return array<mixed>|null
     */
    public function DeleteCurrency(int $currencyId): ?array
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
            //delete
            xtc_db_query("DELETE FROM " . TABLE_CURRENCIES . " WHERE currencies_id = '" . (int)$currencyId . "'");
        }

        $this->logger->info(sprintf('Currency deleted successfully: %s', $currencyId));
        return null;
    }
}
