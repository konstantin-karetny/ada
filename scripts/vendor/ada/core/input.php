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

        public static function get(
            string $name,
            string $filter  = 'cmd',
                   $default = null,
            string $src     = 'request'
        ) {
            switch (strtolower(trim($src))) {
                case 'cookie':
                    // .................
                    $res = $_COOKIE[$name] ?? $default;
                    break;
                case 'get':
                    $res = $_GET[$name] ?? $default;
                    break;
                case 'post':
                    $res = $_POST[$name] ?? $default;
                    break;
                case 'server':
                    $res = $_SERVER[$name] ?? $default;
                    break;
                case 'session':
                    // .................
                    $res = $_SESSION[$name] ?? $default;
                    break;
                default:
                    $res = $_REQUEST[$name] ?? $default;
                    break;
            }
            return Clean::clean($res, $filter);
        }

        public static function getBase64(
                   $name,
            string $default = '',
            string $src     = 'request'
        ) {
            return self::get($name, 'base64', $default, $src);
        }

        public static function getBool(
                   $name,
            string $default = '',
            string $src     = 'request'
        ) {
            return self::get($name, 'bool', $default, $src);
        }

        public static function getCmd(
                   $name,
            string $default = '',
            string $src     = 'request'
        ) {
            return self::get($name, 'cmd', $default, $src);
        }

        public static function getEmail(
                   $name,
            string $default = '',
            string $src     = 'request'
        ) {
            return self::get($name, 'email', $default, $src);
        }

        public static function getHtml(
                   $name,
            string $default = '',
            string $src     = 'request'
        ) {
            return self::get($name, 'html', $default, $src);
        }

        public static function getInt(
                   $name,
            string $default = '',
            string $src     = 'request'
        ) {
            return self::get($name, 'int', $default, $src);
        }

        public static function getFloat(
                   $name,
            string $default = '',
            string $src     = 'request'
        ) {
            return self::get($name, 'float', $default, $src);
        }

        public static function getPath(
                   $name,
            string $default = '',
            string $src     = 'request'
        ) {
            return self::get($name, 'path', $default, $src);
        }

        public static function getStr(
                   $name,
            string $default = '',
            string $src     = 'request'
        ) {
            return self::get($name, 'str', $default, $src);
        }

        public static function getUrl(
                   $name,
            string $default = '',
            string $src     = 'request'
        ) {
            return self::get($name, 'url', $default, $src);
        }

        public static function getWord(
                   $name,
            string $default = '',
            string $src     = 'request'
        ) {
            return self::get($name, 'word', $default, $src);
        }

    }
