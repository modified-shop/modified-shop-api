<?php

/**
 * /includes/external/api/v1/Utility/Hydrator.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Utility;

/**
 * Shared option hydration: keys with a matching setter method are dispatched
 * to it ('secure' => $this->secure($value)), everything else is stored in the
 * using class' $options array property.
 */
trait Hydrator
{
    /**
     * Hydrate options from given array.
     *
     * @param mixed[] $data
     *
     * @return void
     */
    protected function hydrate(array $data = []): void
    {
        foreach ($data as $key => $value) {
            $key = str_replace(".", " ", $key);
            $method = lcfirst(ucwords($key));
            $method = str_replace(" ", "", $method);
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $value);
            } else {
                $this->options[$key] = $value;
            }
        }
    }
}
