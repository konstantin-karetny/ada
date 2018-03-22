<?php
    /**
    * @package   project/core
    * @version   1.0.0 17.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Clean extends Proto {

        public static function base64($val): string {
            return (string) preg_replace('/[^a-z0-9\/+=]/i', '', $val);
        }

        public static function bool($val): bool {
            return (bool) (
                is_numeric($val) ? (1 * $val) : $val
            );
        }

        public static function cmd($val): string {
            return ltrim(
                (string) preg_replace('/[^a-z0-9_\.-]/i', '', $val),
                '.'
            );
        }

        public static function email($val): string {
            return (string) filter_var(trim($val), FILTER_SANITIZE_EMAIL);
        }

        public static function float($val, bool $abs = true): float {
            $res = filter_var(
                $val,
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            return (float) ($abs ? abs($res) : $res);
        }

        public static function html($val, bool $abs = true): string {
            $val = trim($val);
            return (string) (
                preg_match('//u', $val)
                    ? $val
                    : htmlspecialchars_decode(
                        htmlspecialchars($val, ENT_IGNORE, 'UTF-8')
                    )
            );
        }

        public static function int($val, bool $abs = true): int {
            $res = filter_var($val, FILTER_SANITIZE_NUMBER_INT);
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

        public static function path($val, bool $validate_ext = false): string {
            return Path::clean($val, $validate_ext);
        }

        public static function string($val): string {
            return html_entity_decode(trim($val));
        }

        public static function url($val): string {
            return Url::clean($val);
        }

        public static function value($val, string $filter) {
            $method = strtolower(trim($filter));
            if (!method_exists(__CLASS__, $method)) {
                throw new Exception('Wrong filter name \'' . $filter . '\'', 1);
            }
            return static::$method($val);
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

        public static function word($val): string {
            return (string) preg_replace('/[^a-z_]/i', '', $val);
        }

    }