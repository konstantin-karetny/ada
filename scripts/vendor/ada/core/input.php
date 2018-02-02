<?php
    /**
    * @package   ada/core
    * @version   1.0.0 01.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Input extends Singleton {

        const
            DEFAULT_FILTER = 'cmd',
            DEFAULT_SRC    = 'request';

        public static function get(
            string $name,
            string $filter  = self::DEFAULT_FILTER,
                   $default = null,
            string $src     = self::DEFAULT_SRC
        ) {
            switch (strtolower(trim($src))) {
                case 'cookie':
                    // .........................................................
                    $res = $_COOKIE[$name] ?? $default;
                    break;
                case 'get':
                    $res = $_GET[$name] ?? $default;
                    break;
                case 'post':
                    $res = $_POST[$name] ?? $default;
                    break;
                case 'request':
                    $res = $_REQUEST[$name] ?? $default;
                    break;
                case 'server':
                    $res = $_SERVER[$name] ?? $default;
                    break;
                case 'session':
                    // .........................................................
                    $res = $_SESSION[$name] ?? $default;
                    break;
                default:
                    throw new Exception('Wrong source name \'' . $src . '\'', 1);
                    break;
            }
            return (
                is_array($res)
                ? Clean::values($res, $filter)
                : Clean::value ($res, $filter)
            );
        }

        public static function getArray(
                   $name,
            string $filter  = self::DEFAULT_FILTER,
            array  $default = [],
            string $src     = self::DEFAULT_SRC
        ): array {
            return self::get($name, $filter, $default, $src);
        }

        public static function getBase64(
                   $name,
            string $default = '',
            string $src     = self::DEFAULT_SRC
        ): string {
            return self::get($name, 'base64', $default, $src);
        }

        public static function getBool(
                   $name,
            bool   $default = false,
            string $src     = self::DEFAULT_SRC
        ): bool {
            return self::get($name, 'bool', $default, $src);
        }

        public static function getCmd(
                   $name,
            string $default = '',
            string $src     = self::DEFAULT_SRC
        ): string {
            return self::get($name, 'cmd', $default, $src);
        }

        public static function getEmail(
                   $name,
            string $default = '',
            string $src     = self::DEFAULT_SRC
        ): string {
            return self::get($name, 'email', $default, $src);
        }

        public static function getHtml(
                   $name,
            string $default = '',
            string $src     = self::DEFAULT_SRC
        ): string {
            return self::get($name, 'html', $default, $src);
        }

        public static function getInt(
                   $name,
            int    $default = 0,
            string $src     = self::DEFAULT_SRC
        ): int {
            return self::get($name, 'int', $default, $src);
        }

        public static function getFiles($name, array $default = []) {
            $res = [];
            foreach ($_FILES[$name]['name'] as $k => $n) {
                $res[$k] = [
                    'error'    => $_FILES[$name]['error'][$k],
                    //'ext'      => File::getExt($_FILES[$name]['name'][$k]),
                    'name'     => $_FILES[$name]['name'][$k],
                    'size'     => $_FILES[$name]['size'][$k],
                    'tmp_name' => $_FILES[$name]['tmp_name'][$k],
                    'type'     => $_FILES[$name]['type'][$k]
                ];
            }
            return $res;
        }

        public static function getFloat(
                   $name,
            float  $default = 0,
            string $src     = self::DEFAULT_SRC
        ): float {
            return self::get($name, 'float', $default, $src);
        }

        public static function getPath(
                   $name,
            string $default = '',
            string $src     = self::DEFAULT_SRC
        ): string {
            return self::get($name, 'path', $default, $src);
        }

        public static function getString(
                   $name,
            string $default = '',
            string $src     = self::DEFAULT_SRC
        ): string {
            return self::get($name, 'string', $default, $src);
        }

        public static function getUrl(
                   $name,
            string $default = '',
            string $src     = self::DEFAULT_SRC
        ): string {
            return self::get($name, 'url', $default, $src);
        }

        public static function getWord(
                   $name,
            string $default = '',
            string $src     = self::DEFAULT_SRC
        ): string {
            return self::get($name, 'word', $default, $src);
        }

    }
