<?php
    /**
    * @package   ada/tools
    * @version   1.0.0 26.01.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Tools;

    class Type extends \Ada\Core\Proto {
/*
        protected const
            TYPES = [
                'arr' => [
                    'names' => [
                        'arr',
                        'array'
                    ],
                    'value' => []
                ],
                'bool' => [
                    'names' => [
                        'bool',
                        'boolean'
                    ],
                    'value' => false
                ],
                'float' => [
                    'names' => [
                        'float',
                        'double'
                    ],
                    'value' => 0
                ],
                'int' => [
                    'names' => [
                        'int',
                        'integer'
                    ],
                    'value' => 0
                ],
                'null' => [
                    'names' => [
                        'null'
                    ],
                    'value' => null
                ],
                'obj' => [
                    'names' => [
                        'obj',
                        'object'
                    ],
                    'value' => new stdClass
                ],
                'str' => [
                    'names' => [
                        'str',
                        'string'
                    ],
                    'value' => ''
                ],
                'res' => [
                    'names' => [
                        'res',
                        'resource'
                    ],
                    'value' => null
                ],
            ];
*/
        public static function detect($var) {
            if (is_string($var)) {
                if (is_numeric($var)) {
                    
                }


                return is_numeric($var) ? (1 * $var) : $var;
            }
            if (is_array($var)) {
                $arr = [];
                foreach ($var as $key => $val) {
                    $arr[self::typify($key)] = self::typify($val);
                }
                return $arr;
            }
            return $var;
        }

        public static function get($var) {
            $res = gettype($var);
            switch ($res) {
                case 'array':
                    return 'arr';
                case 'boolean':
                    return 'bool';
                case 'double':
                    return (float) (is_numeric($var) ? $var : (bool) $var);
                case 'int':
                case 'integer':
                    return (int) (is_numeric($var) ? $var : (bool) $var);
                case 'null':
                    return null;
                case 'obj':
                case 'object':
                    return (object) $var;
                case 'str':
                case 'string':
                    return (string) $var;
                default:
                    throw new \Ada\Core\Exception('Wrong datatype \'' . $type . '\'');
            }
        }

        public static function set(
                   $var,
            string $type        = 'auto',
            bool   $recursively = false
        ) {
            switch ($type) {
                case 'arr':
                case 'array':
                    return (array) $var;
                case 'auto':
                    exit(var_dump( 'asdfasdf' ));
                case 'bool':
                case 'boolean':
                    return (bool) $var;
                case 'float':
                case 'double':
                    return (float) (is_numeric($var) ? $var : (bool) $var);
                case 'int':
                case 'integer':
                    return (int) (is_numeric($var) ? $var : (bool) $var);
                case 'null':
                    return null;
                case 'obj':
                case 'object':
                    return (object) $var;
                case 'str':
                case 'string':
                    return (string) $var;
                default:
                    throw new \Ada\Core\Exception('Wrong datatype \'' . $type . '\'');
            }
        }

    }
