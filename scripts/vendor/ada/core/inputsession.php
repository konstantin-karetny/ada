<?php
    /**
    * @package   ada/core
    * @version   1.0.0 08.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class InputSession extends Input {

        public static function get(
            string $name,
            string $filter    = 'string',
                   $default   = null,
            string $namespace = Session::DEFAULT_NAMESPACE
        ) {
            $session = Session::init();
            if (!$session->start(true)) {
                return false;
            }
            return Clean::value(
                $_SESSION[$session->namespaceFull($namespace)][Clean::cmd($name)] ?? $default,
                $filter
            );
        }

        public static function getArray(
            string $name,
            string $filter    = 'string',
                   $default   = [],
            string $namespace = Session::DEFAULT_NAMESPACE
        ): array {
            $session = Session::init();
            if (!$session->start(true)) {
                return false;
            }
            return Clean::values(
                $_SESSION[Clean::cmd($name)] ?? $default,
                $filter
            );
        }

        public static function getBase64(
            string $name,
                   $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return self::get($name, 'base64', $default, $namespace);
        }

        public static function getBool(
            string $name,
                   $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): bool {
            return self::get($name, 'bool', $default, $namespace);
        }

        public static function getCmd(
            string $name,
                   $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return self::get($name, 'cmd', $default, $namespace);
        }

        public static function getEmail(
            string $name,
                   $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return self::get($name, 'email', $default, $namespace);
        }

        public static function getHtml(
            string $name,
                   $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return self::get($name, 'html', $default, $namespace);
        }

        public static function getInt(
            string $name,
                   $default   = 0,
            string $namespace = Session::DEFAULT_NAMESPACE
        ): int {
            return self::get($name, 'int', $default, $namespace);
        }

        public static function getFloat(
            string $name,
                   $default   = 0,
            string $namespace = Session::DEFAULT_NAMESPACE
        ): float {
            return self::get($name, 'float', $default, $namespace);
        }

        public static function getPath(
            string $name,
                   $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return self::get($name, 'path', $default, $namespace);
        }

        public static function getString(
            string $name,
                   $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return self::get($name, 'string', $default, $namespace);
        }

        public static function getUrl(
            string $name,
                   $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return self::get($name, 'url', $default, $namespace);
        }

        public static function getWord(
            string $name,
                   $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return self::get($name, 'word', $default, $namespace);
        }

        public static function set(
            string $name,
                   $value     = null,
            string $namespace = Session::DEFAULT_NAMESPACE
        ): bool {
            $session = Session::init();
            if (!$session->start()) {
                return false;
            }
            $_SESSION[$session->namespaceFull($namespace)][Clean::cmd($name)] = $value;
            return true;
        }

        public static function unset(
            string $name,
            string $namespace = Session::DEFAULT_NAMESPACE
        ): bool {
            $session = Session::init();
            if (!$session->start()) {
                return false;
            }
            $namespace_full = $session->namespaceFull($namespace);
            unset($_SESSION[$namespace_full][Clean::cmd($name)]);
            if (!$_SESSION[$namespace_full]) {
                unset($_SESSION[$namespace_full]);
            }
            return true;
        }

    }