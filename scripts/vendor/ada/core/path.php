<?php
    /**
    * @package   ada/core
    * @version   1.0.0 01.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Path extends Singleton {

        const
            DS = '/';

        public static function clean(string $path): string {
            return strtolower(
                (string) preg_replace(
                    '/[\/\\\]+/',
                    self::DS,
                    trim($path, " \t\n\r\0\x0B\\/")
                )
            );
        }

    }
