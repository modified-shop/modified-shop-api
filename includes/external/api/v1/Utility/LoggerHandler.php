<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

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
       * @param string|null $name The logging channel
       *
       * @return LoggerInterface The logger
       */
      public function createLogger(): LoggerInterface
      {
          $logger = new \LoggingManager($this->path.$this->filename, $this->name, $this->level);

          return $logger;
      }
  }
