<?php
    /**
    * @package    package_lib
    * @version    1.0.0 11.10.2016
    * @copyright  copyright
    * @license    Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Lib;

    class ArrayHelper {

        public static function groupByKeys(array $array) {
            $res = [];
            foreach ($array as $array_inner) {
                foreach ($array_inner as $key => $val) {
                    $res[$key][] = $val;
                }
            }
            return $res;
        }

    }
