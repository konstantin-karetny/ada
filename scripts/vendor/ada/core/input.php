<?php
    /**
    * @package   ada/core
    * @version   1.0.0 07.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    abstract class Input extends Proto {

        abstract public static function get(
            string $name,
            string $filter  = 'string',
                   $default = null
        );

        public static function getBase64(string $name, $default = ''): string {
            return static::get($name, 'base64', $default);
        }

        public static function getBool(string $name, $default = false): bool {
            return static::get($name, 'bool', $default);
        }

        public static function getCmd(string $name, $default = ''): string {
            return static::get($name, 'cmd', $default);
        }

        public static function getEmail(string $name, $default = ''): string {
            return static::get($name, 'email', $default);
        }

        public static function getHtml(string $name, $default = ''): string {
            return static::get($name, 'html', $default);
        }

        public static function getInt(string $name, $default = 0): int {
            return static::get($name, 'int', $default);
        }

        public static function getFloat(string $name, $default = 0): float {
            return static::get($name, 'float', $default);
        }

        public static function getPath(string $name, $default = ''): string {
            return static::get($name, 'path', $default);
        }

        public static function getString(string $name, $default = ''): string {
            return static::get($name, 'string', $default);
        }

        public static function getUrl(string $name, $default = ''): string {
            return static::get($name, 'url', $default);
        }

        public static function getWord(string $name, $default = ''): string {
            return static::get($name, 'word', $default);
        }

    }
