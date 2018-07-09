<?php
    /**
    * @package   project/core
    * @version   1.0.0 09.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Inp;

    class Get extends Inp {

        public static function drop(string $name): bool {
            return \Ada\Core\Url::init()->dropVar($name);
        }

        public static function get(
            string $name,
            string $filter,
                   $default = ''
        ) {
            return \Ada\Core\Url::init()->getVar($name, $filter, $default);
        }

        public static function set(string $name, string $value) {
            \Ada\Core\Url::init()->setVar($name, $value);
        }

    }
