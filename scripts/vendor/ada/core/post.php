<?php
    /**
    * @package   ada/core
    * @version   1.0.0 07.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Post extends Input {

        public static function get(
            string $name,
            string $filter  = 'string',
                   $default = ''
        ) {
            return Clean::value(
                $_POST[Clean::cmd($name)] ?? $default,
                $filter
            );
        }

        public static function getArray(
            string $name,
            string $filter  = 'string',
                   $default = []
        ): array {
            return Clean::values(
                $_POST[Clean::cmd($name)] ?? $default,
                $filter
            );
        }

    }
