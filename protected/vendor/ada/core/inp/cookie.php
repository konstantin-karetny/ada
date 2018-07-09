<?php
    /**
    * @package   project/core
    * @version   1.0.0 09.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Inp;

    class Cookie extends Inp {

        public static function drop(string $name): bool {
            $name = \Ada\Core\Clean::cmd($name);
            if (!isset(static::getStorage()[$name])) {
                return true;
            }
            unset(static::getStorage()[$name]);
            return (bool) setcookie($name, '', time() - 1);
        }

        public static function get(
            string $name,
            string $filter,
                   $default = ''
        ) {
            return \Ada\Core\Clean::value(
                static::getStorage()[\Ada\Core\Clean::cmd($name)] ?? $default,
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
            $name                       = \Ada\Core\Clean::cmd($name);
            $value                      = \Ada\Core\Types::set($value);
            static::getStorage()[$name] = $value;
            return (bool) setcookie(
                $name,
                $value,
                $expire,
                $path,
                \Ada\Core\Url::init()->isSSL(),
                $httponly
            );
        }

    }
