<?php
    /**
    * @package   ada/core
    * @version   1.0.0 07.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Cookie extends Input {

        public static function get(
            string $name,
            string $filter  = 'string',
                   $default = ''
        ) {
            return Clean::value(
                $_COOKIE[Clean::cmd($name)] ?? $default,
                $filter
            );
        }

        public static function set(
            string $name,
            string $value    = '',
            int    $expire   = 0,
            string $path     = '',
            bool   $httponly = false
        ): bool {
            $name           = Clean::cmd($name);
            $_COOKIE[$name] = $value;
            return (bool) setcookie(
                $name,
                $value,
                $expire,
                $path,
                Url::init()->isSSL(),
                $httponly
            );
        }

        public static function unset(string $name): bool {
            $name = Clean::cmd($name);
            if (!isset($_COOKIE[$name])) {
                return true;
            }
            unset($_COOKIE[$name]);
            return (bool) setcookie($name, '', time() - 1);
        }

    }
