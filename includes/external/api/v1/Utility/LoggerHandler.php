<?php

/**
 * /includes/external/api/v1/Utility/LoggerHandler.php
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

use Psr\Log\LoggerInterface;

/**
 * Factory.
 */
final class LoggerHandler
{
    /**
     * @var string
     */
    private $path;

    /**
      * @var string
      */
    private $filename;

    /**
      * @var string
      */
    private $name;

    /**
     * @var int
     */
    private $level;

    /**
     * The constructor.
     *
     * @param array<mixed> $settings The settings
     */
    public function __construct(array $settings)
    {
        $this->path = (string)$settings['path'];
        $this->filename = (string)$settings['filename'];
        $this->name = (string)$settings['name'];
        $this->level = (string)$settings['level'];
    }

    /**
     * Build the logger.
     *
     * @return LoggerInterface The logger
     */
    public function createLogger(): LoggerInterface
    {
        $logger = new \LoggingManager($this->path . $this->filename, $this->name, $this->level);

        return $logger;
    }
}
