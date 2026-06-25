<?php

/**
 * /includes/external/api/v1/Action/Manufacturer/ManufacturerGetAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Manufacturer;

use api\v1\Action\BaseAction;
use api\v1\Utility\LoggerHandler;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Service.
 */
trait ManufacturerGetAction
{
    /**
     * Read an manufacturer by the given manufacturer id.
     *
     * @param int $manufacturerId The manufacturer id
     *
     * @throws Exception
     *
     * @return array The manufacturer data
     */
    public function GetManufacturerDetails(int $manufacturerId): array
    {
        // Input validation
        if (empty($manufacturerId)) {
            throw new Exception('Manufacturer ID required');
        }

        $manufacturer_query = xtc_db_query("SELECT *
                                                FROM " . TABLE_MANUFACTURERS . "
                                               WHERE manufacturers_id = '" . (int)$manufacturerId . "'");
        if (xtc_db_num_rows($manufacturer_query) < 1) {
            return $this->errormessage(sprintf('Manufacturer not found: %s', $manufacturerId));
        } else {
            // disable Exception
            $this->throw_exception = false;

            $result = [
                'manufacturers' => $this->GetManufacturer($manufacturerId, false),
                'manufacturers_description' => $this->GetManufacturerDescription($manufacturerId),
            ];

            if (isset($this->options['with'])) {
                $with = explode(',', $this->options['with']);
                if (in_array('products', $with) !== false) {
                    $result['products'] = $this->GetManufacturerProducts($manufacturerId);
                }
            }

            return $result;
        }
    }

    /**
     * Read a manufacturer by the given manufacturer id.
     *
     * @param int $manufacturerId The manufacturer id
     * @param mixed[] $options
     *
     * @throws Exception
     *
     * @return array The manufacturer data
     */
    public function GetSingleManufacturer(int $manufacturerId, array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        // Input validation
        if (empty($manufacturerId)) {
            throw new Exception('Manufacturer ID required');
        }

        $result = $this->GetManufacturerDetails($manufacturerId);
        return $result;
    }

    /**
     * Read manufacturers by given conditions
     *
     * @param mixed[] $options
     *
     * @return array The manufacturer data
     */
    public function GetManufacturers(array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        $conditions = [];
        if (isset($this->options['status']) && !empty(preg_replace('/[^\d\,]/', '', $this->options['status']))) {
            $conditions[] = " manufacturers_status IN (" . preg_replace('/[^\d\,]/', '', $this->options['status']) . ") ";
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
                                         FROM " . TABLE_MANUFACTURERS . "
                                              " . $where);
        $count = xtc_db_fetch_array($count_query);

        if ($count['total'] < 1) {
            return $this->errormessage('no Manufacturer found');
        }

        $data = [];
        $manufacturers_query = xtc_db_query("SELECT manufacturers_id
                                                 FROM " . TABLE_MANUFACTURERS . "
                                                      " . $where . "
                                             ORDER BY date_added DESC
                                                LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
            $data[] = $this->GetManufacturerDetails($manufacturers['manufacturers_id']);
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
     * Read a Manufacturer by the given Manufacturer id.
     *
     * @param int $manufacturerId The Manufacturer id
     *
     * @throws Exception
     *
     * @return array The Manufacturer data
     */
    public function GetManufacturer(int $manufacturerId): array
    {
        // Input validation
        if (empty($manufacturerId)) {
            throw new Exception('Manufacturer ID required');
        }

        $manufacturer = [];
        $manufacturer_query = xtc_db_query("SELECT *
                                                FROM " . TABLE_MANUFACTURERS . "
                                               WHERE manufacturers_id = '" . (int)$manufacturerId . "'");
        if (xtc_db_num_rows($manufacturer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Manufacturer not found: %s', $manufacturerId));
        } else {
            $manufacturer = xtc_db_fetch_array($manufacturer_query);
        }

        $result = $this->encode_request($manufacturer);
        return $result;
    }

    /**
     * Read a manufacturer description by the given manufacturer id.
     *
     * @param int $manufacturerId The manufacturer id
     *
     * @throws Exception
     *
     * @return array The manufacturer data
     */
    public function GetManufacturerDescription(int $manufacturerId): array
    {
        // Input validation
        if (empty($manufacturerId)) {
            throw new Exception('Manufacturer ID required');
        }

        $description = [];
        $manufacturer_query = xtc_db_query("SELECT *
                                                FROM " . TABLE_MANUFACTURERS_INFO . "
                                               WHERE manufacturers_id = '" . (int)$manufacturerId . "'");
        if (xtc_db_num_rows($manufacturer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Manufacturer description not found: %s', $manufacturerId));
        } else {
            $manufacturers_description_query = xtc_db_query("SELECT mi.*,
                                                                      l.code
                                                                 FROM " . TABLE_MANUFACTURERS_INFO . " mi
                                                                 JOIN " . TABLE_LANGUAGES . " l
                                                                      ON l.languages_id = mi.languages_id
                                                                WHERE mi.manufacturers_id = '" . (int)$manufacturerId . "'");
            while ($manufacturers_description = xtc_db_fetch_array($manufacturers_description_query)) {
                $code = $manufacturers_description['code'];
                unset($manufacturers_description['code']);

                $description[$code] = $manufacturers_description;
            }
        }

        $result = $this->encode_request($description);
        return $result;
    }

    /**
     * Read a Manufacturer categories by the given Manufacturer id.
     *
     * @param int $manufacturerId The Manufacturer id
     *
     * @throws Exception
     *
     * @return array The Manufacturer data
     */
    public function GetManufacturerProducts(int $manufacturerId): array
    {
        // Input validation
        if (empty($manufacturerId)) {
            throw new Exception('Manufacturer ID required');
        }

        $products = [];
        $manufacturer_query = xtc_db_query("SELECT *
                                                FROM " . TABLE_PRODUCTS . "
                                               WHERE manufacturers_id = '" . (int)$manufacturerId . "'");
        if (xtc_db_num_rows($manufacturer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Manufacturer products not found: %s', $manufacturerId));
        } else {
            $manufacturers_products_query = xtc_db_query("SELECT products_id,
                                                                   manufacturers_id
                                                              FROM " . TABLE_PRODUCTS . "
                                                             WHERE manufacturers_id = '" . (int)$manufacturerId . "'
                                                          ORDER BY products_id");
            while ($manufacturers_products = xtc_db_fetch_array($manufacturers_products_query)) {
                $products[] = $manufacturers_products;
            }
        }

        $result = $this->encode_request($products);
        return $result;
    }
}
