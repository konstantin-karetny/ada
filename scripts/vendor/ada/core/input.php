<?php
    /**
    * @package   ada/core
    * @version   1.0.0 09.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    abstract class Input extends Proto {

        abstract public static function get(
            string $name,
            string $filter  = 'auto',
                   $default = null
        );

        public static function getBase64(
            string $name,
            string $default = ''
        ): string {
            return static::get($name, 'base64', $default);
        }

        public static function getBool(
            string $name,
            bool   $default = false
        ): bool {
            return static::get($name, 'bool', $default);
        }

        public static function getCmd(
            string $name,
            string $default = ''
        ): string {
            return static::get($name, 'cmd', $default);
        }

        public static function getEmail(
            string $name,
            string $default = ''
        ): string {
            return static::get($name, 'email', $default);
        }

        public static function getFrstExisting(
            array  $names,
            string $filter  = 'auto',
            string $default = ''
        ): string {
            foreach ($names as $name) {
                if (key_exists(Clean::cmd($name), static::getStorage())) {
                    return static::get($name, $filter);
                }
            }
            return Clean::value($default, $filter);
        }

        public static function getHtml(
            string $name,
            string $default = ''
        ): string {
            return static::get($name, 'html', $default);
        }

        public static function getInt(
            string $name,
            int $default = 0
        ): int {
            return static::get($name, 'int', $default);
        }

        public static function getFloat(
            string $name,
            float  $default = 0
        ): float {
            return static::get($name, 'float', $default);
        }

        public static function getPath(
            string $name,
            string $default = ''
        ): string {
            return static::get($name, 'path', $default);
        }

        public static function getStorage(): array {
            return $GLOBALS[
                '_' .
                strtoupper(
                    substr(strrchr(get_called_class(), '\\'), 1)
                )
            ];
        }

        public static function getString(
            string $name,
            string $default = ''
        ): string {
            return static::get($name, 'string', $default);
        }

        public static function getUrl(
            string $name,
            string $default = ''
        ): string {
            return static::get($name, 'url', $default);
        }

        public static function getWord(
            string $name,
            string $default = ''
        ): string {
            return static::get($name, 'word', $default);
        }

    }
