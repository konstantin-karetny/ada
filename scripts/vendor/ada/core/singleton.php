<?php
    /**
    * @package   ada/core
    * @version   1.0.0 09.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    abstract class Singleton extends Proto {

        private static
            $instances = [];

        protected
            $id = '';

        public static function init(string $id = '', bool $cached = true) {
            $id    = Type::set($id);
            $class = get_called_class();
            if (isset( self::$instances[$class][$id]) && $cached) {
                return self::$instances[$class][$id];
            };
            self::$instances[$class][$id] = new $class(...func_get_args());
            self::$instances[$class][$id]->id = $id;
            return self::$instances[$class][$id];
        }

        protected function __construct() {}

        public function getId() {
            return $this->id;
        }

        private function __clone() {}

    }
