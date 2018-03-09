<?php
    /**
    * @package   ada/core
    * @version   1.0.0 09.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Post extends Input {

        public static function get(
            string $name,
            string $filter  = 'auto',
                   $default = ''
        ) {
            return Clean::value(
                $_POST[Clean::cmd($name)] ?? $default,
                $filter
            );
        }

        public static function getArray(
            string $name,
            string $filter  = 'auto',
                   $default = []
        ): array {
            return Clean::values(
                $_POST[Clean::cmd($name)] ?? $default,
                $filter
            );
        }

    }
