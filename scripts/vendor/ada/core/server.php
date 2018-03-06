<?php
    /**
    * @package   ada/core
    * @version   1.0.0 06.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Server extends Input {

        public static function get(
            string $name,
            string $filter  = 'string',
                   $default = ''
        ) {
            return Clean::value(
                $_SERVER[Clean::cmd($name)] ?? $default,
                $filter
            );
        }

    }
