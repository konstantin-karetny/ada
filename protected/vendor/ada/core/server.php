<?php
    /**
    * @package   project/core
    * @version   1.0.0 21.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Server extends Input {

        public static function get(
            string $name,
            string $filter,
                   $default = ''
        ) {
            return Clean::value(
                $_SERVER[Clean::cmd($name, false)] ?? $default,
                $filter
            );
        }

    }
