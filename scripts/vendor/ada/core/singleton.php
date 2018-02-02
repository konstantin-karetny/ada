<?php
    /**
    * @package   ada/core
    * @version   1.0.0 02.02.2018
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

        public static function init(string $id, bool $cached = true) {
            $class = get_called_class();
            if (isset(self::$instances[$class][$id]) && $cached) {
                return self::$instances[$class][$id];
            }
            self::$instances[$class][$id]     = new $class($id, $cached);
            self::$instances[$class][$id]->id = $id;
            return self::$instances[$class][$id];
        }

        protected function __construct(string $id, bool $cached = true) {}

        public function getId() {
            return $this->id;
        }

        private function __clone() {}

    }
