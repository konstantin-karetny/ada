<?php
    /**
    * @package   project/core
    * @version   1.0.0 09.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Inp;

    abstract class Inp extends \Ada\Core\Proto {

        abstract public static function get(
            string $name,
            string $filter,
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

        public static function getFloat(
            string $name,
            float  $default = 0
        ): float {
            return static::get($name, 'float', $default);
        }

        public static function getFirstExisting(
            array  $names,
            string $filter,
            string $default = ''
        ): string {
            foreach ($names as $name) {
                if (
                    key_exists(
                        \Ada\Core\Clean::cmd($name),
                        static::getStorage()
                    )
                ) {
                    return static::get($name, $filter);
                }
            }
            return \Ada\Core\Clean::value($default, $filter);
        }

        public static function getHtml(
            string $name,
            string $default = ''
        ): string {
            return static::get($name, 'html', $default);
        }

        public static function getInt(
            string $name,
            int    $default = 0
        ): int {
            return static::get($name, 'int', $default);
        }

        public static function getPath(
            string $name,
            string $default = ''
        ): string {
            return static::get($name, 'path', $default);
        }

        public static function &getStorage(): array {
            return $GLOBALS[
                substr(static::getStorageName(), 1)
            ];
        }

        public static function getStorageName(): string {
            return
                '$_' .
                strtoupper(
                    substr(
                        strrchr(get_called_class(), '\\'),
                        1
                    )
                );
        }

        public static function getStr(
            string $name,
            string $default = ''
        ): string {
            return static::get($name, 'str', $default);
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
