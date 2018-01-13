<?php
    /**
    * @package   ada/core
    * @version   1.0.0 13.01.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    abstract class Singleton extends Proto {

        private static $instances = [];

        public static function getInst(
            string $id,
            array  $params = [],
            bool   $cached = true
        ) {
            $class = get_called_class();
            if (isset(self::$instances[$class][$id]) && $cached) {
                return self::$instances[$class][$id];
            }
            return new $class($id, ...$params);
        }

        private function __clone() {}

    }
