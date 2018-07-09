<?php
    /**
    * @package   project/core
    * @version   1.0.0 09.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Inp;

    class Server extends Inp {

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
