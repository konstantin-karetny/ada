<?php
    /**
    * @package   project/core
    * @version   1.0.0 06.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Input;

    class Server extends Input {

        public static function get(
            string $name,
            string $filter,
                   $default = ''
        ) {
            return \Ada\Core\Clean::value(
                static::getStorage()[\Ada\Core\Clean::cmd($name, false)] ?? $default,
                $filter
            );
        }

    }
