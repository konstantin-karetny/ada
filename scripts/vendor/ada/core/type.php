<?php
    /**
    * @package   ada/core
    * @version   1.0.0 07.02.2018
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
            if ($type == 'auto') {
                $type = self::get($val);
            }
            if (!settype($val, strtolower(trim($type)))) {
                throw new Exception('Failed to set type \'' . $type . '\'', 1);
            }
            return $val;
        }

    }
