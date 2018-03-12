<?php
    /**
    * @package   ada/core
    * @version   1.0.0 12.03.2018
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

        public static function float($val, bool $abs = true): float {
            $res = filter_var(
                $val,
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            return (float) ($abs ? abs($res) : $res);
        }

        public static function null() {
            return null;
        }

        public static function path($val): string {
            return Path::clean($val);
        }

        public static function string($val): string {
            return html_entity_decode(trim($val));
        }

        public static function url($val): string {
            return Url::clean($val);
        }

        public static function value($val, string $filter = 'auto') {
            $filter = strtolower(trim($filter));
            if ($filter == 'auto') {
                $filter = Type::get($val);
            }
            if (!method_exists(__CLASS__, $filter)) {
                throw new Exception('Wrong filter name \'' . $filter . '\'', 1);
            }
            return static::$filter($val);
        }

        public static function values($set, string $filter = 'auto') {
            if (!is_array($set) && !is_object($set)) {
                $set = Type::set($set, 'array');
            }
            $is_array = is_array($set);
            foreach ($set as $k => $v) {
                $v = (
                    is_array($v) || is_object($v)
                        ? static::values($v, $filter)
                        : static::value($v, $filter)
                );
                $is_array
                    ? $set[$k] = $v
                    : $set->$k = $v;
            }
            return $set;
        }

        public static function word($val): string {
            return (string) preg_replace('/[^a-z_]/i', '', $val);
        }

    }
