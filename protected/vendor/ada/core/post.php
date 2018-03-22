<?php
    /**
    * @package   project/core
    * @version   1.0.0 17.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Post extends Input {

        public static function get(
            string $name,
            string $filter,
                   $default = ''
        ) {
            return Clean::value(
                $_POST[Clean::cmd($name)] ?? $default,
                $filter
            );
        }

        public static function getArray(
            string $name,
            string $filter  = '',
            array  $default = []
        ): array {
            $res = Type::set($_POST[Clean::cmd($name)] ?? $default);
            return $filter ? Clean::values($res, $filter) : $res;
        }

    }
