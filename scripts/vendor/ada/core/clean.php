<?php
    /**
    * @package   ada/core
    * @version   1.0.0 01.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Clean extends Proto {

        public static function base64(string $val): string {
            return (string) preg_replace('/[^a-z0-9\/+=]/i', '', $val);
        }

        public static function bool(string $val): bool {
            return (bool) (
                is_numeric($val) ? (1 * $val) : $val
            );
        }

        public static function cmd(string $val): string {
            return ltrim(
                (string) preg_replace('/[^a-z0-9_\.-]/i', '', $val),
                '.'
            );
        }

        public static function email(string $val): string {
            return (string) filter_var($val, FILTER_SANITIZE_EMAIL);
        }

        public static function html(string $val, bool $abs = true): string {
            return (string) (
                preg_match('//u', $val)
                ? $val
                : htmlspecialchars_decode(
                    htmlspecialchars($val, ENT_IGNORE, 'UTF-8')
                )
            );
        }

        public static function int(string $val, bool $abs = true): int {
            $res = (int) filter_var($val, FILTER_SANITIZE_NUMBER_INT);
            return $abs ? abs($res) : $res;
        }

        public static function float(string $val, bool $abs = true): float {
            $res = (float) filter_var(
                $val,
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            return $abs ? abs($res) : $res;
        }

        public static function path(string $val): string {
            return Path::clean($val);
        }

        public static function string(string $val): string {
            return html_entity_decode($val);
        }

        public static function url(string $val): string {
            return Url::clean($val);
        }

        public static function value(string $val, string $filter = 'string') {
            $filter = strtolower(trim($filter));
            if (!method_exists(__CLASS__, $filter)) {
                throw new Exception('Wrong filter name \'' . $filter . '\'', 1);
            }
            return self::$filter($val);
        }

        public static function values(
            array  $array,
            string $filter = 'string'
        ): array {
            $filter = strtolower(trim($filter));
            if (!method_exists(__CLASS__, $filter)) {
                throw new Exception('Wrong filter name \'' . $filter . '\'', 1);
            }
            foreach ($array as $k => $v) {
                $array[$k] = (
                    is_array($v)
                    ? self::values($v, $filter)
                    : self::$filter($v)
                );
            }
            return $array;
        }

        public static function word(string $val): string {
            return (string) preg_replace('/[^a-z_]/i', '', $val);
        }

    }
