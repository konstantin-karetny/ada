<?php
    /**
    * @package   ada/core
    * @version   1.0.0 01.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Type extends Proto {

        const
            INITIAL_VALUES = [
                'array'    => [],
                'bool'     => false,
                'float'    => 0.0,
                'int'      => 0,
                'null'     => null,
                'object'   => null,
                'resource' => null,
                'string'   => ''
            ],
            NAMES = [
                'array'    => ['array'],
                'bool'     => ['bool', 'boolean'],
                'float'    => ['float', 'double'],
                'int'      => ['int', 'integer'],
                'null'     => ['null'],
                'object'   => ['object'],
                'resource' => ['resource'],
                'string'   => ['string']
            ];

        public static function get($val): string {
            if (is_string($val) && is_numeric($val)) {
                $val = 1 * $val;
            }
            $type = strtolower(gettype($val));
            return key(
                array_filter(
                    self::NAMES,
                    function($el) use($type) {
                        return in_array($type, $el);
                    }
                )
            );
        }

        public static function set(
                   $val,
            string $type        = 'auto',
            bool   $recursively = false
        ) {
            if ($recursively && is_array($val)) {
                return array_map(
                    function($el) use($type) {
                        return self::set($el, $type, true);
                    },
                    $val
                );
            }
            switch (strtolower(trim($type))) {
                case 'array':
                    return (array) $val;
                case 'auto':
                    return self::set($val, self::get($val));
                case 'bool':
                case 'boolean':
                    return (bool) (
                        is_numeric($val) ? (1 * $val) : $val
                    );
                case 'float':
                case 'double':
                    $res = (float) $val;
                    return ($val && $res ? $res : !!$val);
                case 'int':
                case 'integer':
                    $res = (int) $val;
                    return (int) ($val && $res ? $res : !!$val);
                case 'null':
                    return null;
                case 'object':
                    return (object) $val;
                case 'resource':
                    return null;
                case 'string':
                    return (string) $val;
                default:
                    throw new Exception('Wrong datatype \'' . $type . '\'', 1);
            }
        }

        public static function array($val, bool $recursively = false): array {
            return self::set($val, __FUNCTION__, $recursively);
        }

        public static function bool($val, bool $recursively = false): bool {
            return self::set($val, __FUNCTION__, $recursively);
        }

        public static function float(
                 $val,
            bool $recursively = false,
            bool $abs         = true
        ): float {
            $res = self::set($val, __FUNCTION__, $recursively);
            return $abs ? abs($res) : $res;
        }

        public static function int(
                 $val,
            bool $recursively = false,
            bool $abs         = true
        ): int {
            $res = self::set($val, __FUNCTION__, $recursively);
            return $abs ? abs($res) : $res;
        }

        public static function null($val, bool $recursively = false) {
            return self::set($val, __FUNCTION__, $recursively);
        }

        public static function obj($val, bool $recursively = false) {
            return self::set($val, __FUNCTION__, $recursively);
        }

        public static function resource($val, bool $recursively = false) {
            return self::set($val, __FUNCTION__, $recursively);
        }

        public static function string($val, bool $recursively = false): string {
            return self::set($val, __FUNCTION__, $recursively);
        }

    }
