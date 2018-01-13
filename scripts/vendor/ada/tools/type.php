<?php
    /**
    * @package   ada/tools
    * @version   1.0.0 13.01.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Tools;

    class Type extends \Ada\Core\Proto {

        public static function set($var, string $type) {
            switch ($type) {
                case 'arr':
                case 'array':
                    return (array) $var;
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

        public static function typify($var) {
            if (is_string($var)) {
                $var = trim($var);
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

    }
