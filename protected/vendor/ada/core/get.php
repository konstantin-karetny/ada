<?php
    /**
    * @package   project/core
    * @version   1.0.0 08.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Get extends Input {

        public static function drop(string $name): bool {
            return Url::init()->dropVar($name);
        }

        public static function get(
            string $name,
            string $filter,
                   $default = ''
        ) {
            return Url::init()->getVar($name, $filter, $default);
        }

        public static function set(string $name, string $value) {
            Url::init()->setVar($name, $value);
        }

    }
