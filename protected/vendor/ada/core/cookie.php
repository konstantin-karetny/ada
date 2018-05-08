<?php
    /**
    * @package   project/core
    * @version   1.0.0 08.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Cookie extends Input {

        public static function drop(string $name): bool {
            $name = Clean::cmd($name);
            if (!isset($_COOKIE[$name])) {
                return true;
            }
            unset($_COOKIE[$name]);
            return (bool) setcookie($name, '', time() - 1);
        }

        public static function get(
            string $name,
            string $filter,
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
            $value          = Type::set($value);
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

    }
