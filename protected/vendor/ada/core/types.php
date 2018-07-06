<?php
    /**
    * @package   project/core
    * @version   1.0.0 06.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Types extends Proto {

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
            NAMES          = [
                'array'    => ['array', 'arr'],
                'bool'     => ['bool', 'boolean'],
                'float'    => ['float', 'double'],
                'int'      => ['int', 'integer'],
                'null'     => ['null'],
                'object'   => ['object', 'obj'],
                'resource' => ['resource'],
                'string'   => ['string', 'str']
            ];

        public static function get($val): string {
            if (is_string($val) && is_numeric($val)) {
                $val = 1 * $val;
            }
            return static::getFullName(gettype($val));
        }

        public static function getFullName(string $alias): string {
            return (string) key(
                array_filter(
                    static::NAMES,
                    function($el) use($alias) {
                        return in_array(Clean::cmd($alias), $el);
                    }
                )
            );
        }

        public static function set(
                   $val,
            string $type        = 'auto',
            bool   $recursively = true
        ) {
            if ($recursively) {
                if (is_array($val)) {
                    return array_map(
                        function($el) use($type) {
                            return static::set($el, $type, true);
                        },
                        $val
                    );
                }
                elseif (is_object($val)) {
                    foreach ($val as $k => $v) {
                        $val->$k = static::set($v, $type, true);
                    }
                    return $val;
                }
            }
            $type_full = (
                Clean::cmd($type) === 'auto'
                    ? static::get($val)
                    : static::getFullName($type)
            );
            if(!$type_full) {
                throw new Exception('Unknown type \'' . $type . '\'', 1);
            }
            if (!@settype($val, $type_full)) {
                throw new Exception('Failed to set type \'' . $type . '\'', 2);
            }
            return $val;
        }

    }
