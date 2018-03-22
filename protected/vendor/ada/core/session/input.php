<?php
    /**
    * @package   project/core
    * @version   1.0.0 17.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Session;

    use Ada\Core\Session as Session;

    class Input extends \Ada\Core\Input {

        public static function del(
            string $name,
            string $namespace = Session::DEFAULT_NAMESPACE
        ): bool {
            $session = Session::init();
            if (!$session->start()) {
                return false;
            }
            $namespace_full = $session->namespaceFull($namespace);
            unset($_SESSION[$namespace_full][\Ada\Core\Clean::cmd($name)]);
            if (!$_SESSION[$namespace_full]) {
                unset($_SESSION[$namespace_full]);
            }
            return true;
        }

        public static function get(
            string $name,
            string $filter,
                   $default   = null,
            string $namespace = Session::DEFAULT_NAMESPACE
        ) {
            $session = Session::init();
            return \Ada\Core\Clean::value(
                $session->start(true)
                    ? (
                        $_SESSION[
                            $session->namespaceFull($namespace)
                        ][
                            \Ada\Core\Clean::cmd($name)
                        ] ?? $default
                    )
                    : $default,
                $filter
            );
        }

        public static function getArray(
            string $name,
            string $filter    = '',
            array  $default   = [],
            string $namespace = Session::DEFAULT_NAMESPACE
        ): array {
            $session = Session::init();
            $res     = \Ada\Core\Type::set(
                $session->start(true)
                    ? (
                        $_SESSION[
                            $session->namespaceFull($namespace)
                        ][
                            \Ada\Core\Clean::cmd($name)
                        ] ?? $default
                    )
                    : $default
            );
            return $filter ? \Ada\Core\Clean::values($res, $filter) : $res;
        }

        public static function getBase64(
            string $name,
            string $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return Session::get($name, 'base64', $default, $namespace);
        }

        public static function getBool(
            string $name,
            bool   $default   = false,
            string $namespace = Session::DEFAULT_NAMESPACE
        ): bool {
            return Session::get($name, 'bool', $default, $namespace);
        }

        public static function getCmd(
            string $name,
            string $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return Session::get($name, 'cmd', $default, $namespace);
        }

        public static function getEmail(
            string $name,
            string $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return Session::get($name, 'email', $default, $namespace);
        }

        public static function getFloat(
            string $name,
            float  $default   = 0,
            string $namespace = Session::DEFAULT_NAMESPACE
        ): float {
            return Session::get($name, 'float', $default, $namespace);
        }

        public static function getHtml(
            string $name,
            string $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return Session::get($name, 'html', $default, $namespace);
        }

        public static function getInt(
            string $name,
            int    $default   = 0,
            string $namespace = Session::DEFAULT_NAMESPACE
        ): int {
            return Session::get($name, 'int', $default, $namespace);
        }

        public static function getPath(
            string $name,
            string $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return Session::get($name, 'path', $default, $namespace);
        }

        public static function getString(
            string $name,
            string $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return Session::get($name, 'string', $default, $namespace);
        }

        public static function getUrl(
            string $name,
            string $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return Session::get($name, 'url', $default, $namespace);
        }

        public static function getWord(
            string $name,
            string $default   = '',
            string $namespace = Session::DEFAULT_NAMESPACE
        ): string {
            return Session::get($name, 'word', $default, $namespace);
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
            $_SESSION[
                $session->namespaceFull($namespace)
            ][
                \Ada\Core\Clean::cmd($name)
            ] = \Ada\Core\Type::set($value);
            return true;
        }

    }
