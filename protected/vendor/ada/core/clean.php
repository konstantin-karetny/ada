<?php
    /**
    * @package   project/core
    * @version   1.0.0 09.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Clean extends Proto {

        public static function base64(string $value): string {
            return (string) preg_replace('/[^a-z0-9\/+=]/i', '', $value);
        }

        public static function bool($value): bool {
            return (bool) (is_numeric($value) ? (1 * $value) : $value);
        }

        public static function classname(string $value): string {
            return rtrim(
                ltrim(
                    (string) preg_replace('/[^a-z0-9_\\\]/i', '', $value),
                    implode('', Type\Integer::NUMBERS) . '\\'
                ),
                '\\'
            );
        }

        public static function cmd(
            string $value,
            bool   $lower_case = true
        ): string {
            $res = ltrim(
                (string) preg_replace('/[^a-z0-9_\.-]/i', '', $value),
                '.'
            );
            return $lower_case ? strtolower($res) : $res;
        }

        public static function email(string $value): string {
            return (string) filter_var(trim($value), FILTER_SANITIZE_EMAIL);
        }

        public static function float(string $value, bool $abs = true): float {
            $res = filter_var(
                $value,
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            return (float) ($abs ? abs($res) : $res);
        }

        public static function html(string $value, bool $abs = true): string {
            $value = trim($value);
            return (string) (
                preg_match('//u', $value)
                    ? $value
                    : htmlspecialchars_decode(
                        htmlspecialchars($value, ENT_IGNORE, 'UTF-8')
                    )
            );
        }

        public static function int(string $value, bool $abs = true): int {
            $res = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            return (int) ($abs ? abs($res) : $res);
        }

        public static function null() {
            return null;
        }

        public static function obj($object, string $filter) {
            if (!is_object($object)) {
                throw new Exception(
                    '
                        Argument 1 passed to ' . __METHOD__ . '()
                        must be of the type object, ' .
                        Types::get($object) . ' given
                    ',
                    1
                );
            }
            foreach ($object as $k => $v) {
                $object->$k = static::value($v, $filter);
            }
            return $object;
        }

        public static function path(
            string $value,
            bool   $validate_ext = false
        ): string {
            return Fs\Path::clean($value, $validate_ext);
        }

        public static function str(string $value): string {
            return html_entity_decode(trim($value));
        }

        public static function url(string $value): string {
            return Url::clean($value);
        }

        public static function value(string $value, string $filter) {
            $method = static::cmd($filter);
            if (method_exists(__CLASS__, $method)) {
                return static::$method($value);
            }
            foreach (Types::NAMES[Types::getFullName($method)] ?? [] as $method) {
                if (method_exists(__CLASS__, $method)) {
                    return static::$method($value);
                }
            }
            throw new Exception('Unknown filter \'' . $filter . '\'', 2);
        }

        public static function values(
            array  $array,
            string $filter,
            bool   $recursively = false
        ): array {
            foreach ($array as $k => $v) {
                $array[$k] = (
                    is_array($v)
                        ? ($recursively ? static::values($v, $filter) : $v)
                        : static::value($v, $filter)
                );
            }
            return $array;
        }

        public static function word(string $value): string {
            return (string) preg_replace('/[^a-z_]/i', '', $value);
        }

    }
