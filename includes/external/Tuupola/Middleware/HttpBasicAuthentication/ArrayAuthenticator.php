<?php

declare(strict_types=1);

/*

Copyright (c) 2013-2020 Mika Tuupola

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

/**
 * @see       https://github.com/tuupola/slim-basic-auth
 * @license   https://www.opensource.org/licenses/mit-license.php
 */

namespace Tuupola\Middleware\HttpBasicAuthentication;

final class ArrayAuthenticator implements AuthenticatorInterface
{
    /**
     * Stores all the options passed to the authenticator.
     * @var mixed[]
     */
    private $options;

    /**
     * @param mixed[] $options
     */
    public function __construct(array $options = [])
    {

        /* Default options. */
        $this->options = [
            "users" => []
        ];

        if ($options) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * @param string[] $arguments
     */
    public function __invoke(array $arguments): bool
    {
        $user = $arguments["user"];
        $password = $arguments["password"];

        /* Unknown user. */
        if (!isset($this->options["users"][$user])) {
            return false;
        }
        
        if (self::isHash($this->options["users"][$user])) {
            // Check that password is good
            require_once (DIR_FS_INC.'xtc_validate_password.inc.php');
            return xtc_validate_password($password, $this->options["users"][$user]);
        } else {
            if ($this->options["users"][$user] === $password || $this->options["users"][$user] === md5($password)) return true;
            return false; 
        }
    }

    private static function isHash(string $password): bool
    {
        return preg_match('/^\$(2|2a|2y)\$\d{2}\$.*/', $password) && (strlen($password) >= 60);
    }
}
