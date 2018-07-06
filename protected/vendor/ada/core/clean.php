<?php
    /**
    * @package   project/core
    * @version   1.0.0 21.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Clean extends Proto {

        public static function base64($value): string {
            return (string) preg_replace('/[^a-z0-9\/+=]/i', '', $value);
        }

        public static function bool($value): bool {
            return (bool) (is_numeric($value) ? (1 * $value) : $value);
        }

        public static function cmd($value, bool $lower_case = true): string {
            $res = ltrim(
                (string) preg_replace('/[^a-z0-9_\.-]/i', '', $value),
                '.'
            );
            return $lower_case ? strtolower($res) : $res;
        }

        public static function email($value): string {
            return (string) filter_var(trim($value), FILTER_SANITIZE_EMAIL);
        }

        public static function float($value, bool $abs = true): float {
            $res = filter_var(
                $value,
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            return (float) ($abs ? abs($res) : $res);
        }

        public static function html($value, bool $abs = true): string {
            $value = trim($value);
            return (string) (
                preg_match('//u', $value)
                    ? $value
                    : htmlspecialchars_decode(
                        htmlspecialchars($value, ENT_IGNORE, 'UTF-8')
                    )
            );
        }

        public static function int($value, bool $abs = true): int {
            $res = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            return (int) ($abs ? abs($res) : $res);
        }

        public static function null() {
            return null;
        }

        public static function object($object, string $filter) {
            foreach ($object as $k => $v) {
                $object->$k = static::value($v, $filter);
            }
            return $object;
        }

        public static function path($value, bool $valueidate_ext = false): string {
            return Path::clean($value, $valueidate_ext);
        }

        public static function string($value): string {
            return html_entity_decode(trim($value));
        }

        public static function url($value): string {
            return Url::clean($value);
        }

        public static function value($value, string $filter) {
            $method = static::cmd($filter);
            if (!method_exists(__CLASS__, $method)) {
                $method = Types::getFullName($method);
            }
            if (!method_exists(__CLASS__, $method)) {
                throw new Exception('Unknown filter \'' . $filter . '\'', 1);
            }
            return static::$method($value);
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

        public static function word($value): string {
            return (string) preg_replace('/[^a-z_]/i', '', $value);
        }

    }
