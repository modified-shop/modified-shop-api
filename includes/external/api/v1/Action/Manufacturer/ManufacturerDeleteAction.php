<?php

/**
 * /includes/external/api/v1/Action/Manufacturer/ManufacturerDeleteAction.php
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

use Exception;

/**
 * Service.
 */
trait ManufacturerDeleteAction
{
    /**
     * Delete a manufacturer by the given manufacturer id.
     *
     * @param int $manufacturerId The manufacturer id
     *
     * @throws Exception
     *
     * @return array<mixed>|null
     */
    public function DeleteManufacturer(int $manufacturerId): ?array
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

            //delete details
            $this->DeleteImage($manufacturerId);
            $this->DeleteAllProducts($manufacturerId);

            //delete
            xtc_db_query("DELETE FROM " . TABLE_MANUFACTURERS . " WHERE manufacturers_id = '" . (int)$manufacturerId . "'");
            xtc_db_query("DELETE FROM " . TABLE_MANUFACTURERS_INFO . " WHERE manufacturers_id = '" . (int)$manufacturerId . "'");
        }

        $this->logger->info(sprintf('Manufacturer deleted successfully: %s', $manufacturerId));
        return null;
    }

    /**
     * Delete a manufacturer by the given manufacturer id and product id.
     *
     * @param int $manufacturerId The manufacturer id
     * @param int $productId The product id
     *
     * @throws Exception
     *
     * @return array<mixed>|null
     */
    public function DeleteProduct(int $manufacturerId, int $productId): ?array
    {
        // Input validation
        if (empty($manufacturerId)) {
            throw new Exception('Manufacturer ID required');
        }

        $where = '';
        if ($productId > 0) {
            $where = "AND products_id = '" . (int)$productId . "'";
        }

        $manufacturer_query = xtc_db_query("SELECT *
                                                FROM " . TABLE_PRODUCTS . "
                                               WHERE manufacturers_id = '" . (int)$manufacturerId . "'
                                                     " . $where);
        if (xtc_db_num_rows($manufacturer_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Manufacturer product not found: %s', $manufacturerId));
        } else {
            while ($manufacturer = xtc_db_fetch_array($manufacturer_query)) {
                xtc_db_query("UPDATE " . TABLE_PRODUCTS . "
                                   SET manufacturers_id = '0'
                                 WHERE products_id = '" . (int)$manufacturer['products_id'] . "'");
            }
        }
        return null;
    }

    /**
     * Delete all manufacturers by the given manufacturer id.
     *
     * @param int $manufacturerId The manufacturer id
     *
     * @throws Exception
     *
     * @return array<mixed>|null
     */
    public function DeleteAllProducts(int $manufacturerId): ?array
    {
        // Input validation
        if (empty($manufacturerId)) {
            throw new Exception('Manufacturer ID required');
        }

        return $this->DeleteProduct($manufacturerId, 0);
    }

    /**
     * Delete a image by the given manufacturer id.
     *
     * @param int $manufacturerId The manufacturer id
     *
     * @throws Exception
     *
     * @return array<mixed>|null
     */
    public function DeleteImage(int $manufacturerId): ?array
    {
        // Input validation
        if (empty($manufacturerId)) {
            throw new Exception('Manufacturer ID required');
        }

        $images_query = xtc_db_query("SELECT *
                                          FROM " . TABLE_MANUFACTURERS . "
                                         WHERE manufacturers_id = '" . (int)$manufacturerId . "'
                                           AND manufacturers_image != ''");
        if (xtc_db_num_rows($images_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Manufacturer image not found: %s', $manufacturerId));
        } else {
            $images = xtc_db_fetch_array($images_query);

            $this->deleteImageFile($images['manufacturers_image']);

            xtc_db_query("UPDATE " . TABLE_MANUFACTURERS . "
                               SET manufacturers_image = ''
                             WHERE manufacturers_id = '" . (int)$manufacturerId . "'");
        }
        return null;
    }

    /**
     * Deletes an image by given name.
     *
     * @param string $image_name
     *
     * @throws Exception
     *
     * @return void
     */
    private function deleteImageFile($image_name): void
    {
        $total = 0;
        $manufacturer_image_query = xtc_db_query("SELECT COUNT(*) AS total 
                                                      FROM " . TABLE_MANUFACTURERS . " 
                                                     WHERE manufacturers_image = '" . xtc_db_input($image_name) . "'");
        $manufacturer_image = xtc_db_fetch_array($manufacturer_image_query);
        $total += $manufacturer_image['total'];

        if ($total < 2) {
            //xtc_del_image_file($image_name);
        }
    }
}
