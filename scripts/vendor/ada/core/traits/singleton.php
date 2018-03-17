<?php
    /**
    * @package   ada/core
    * @version   1.0.0 17.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Traits;

    trait Singleton {

        private static
            $insts = [];

        protected
            $id    = '';

        public static function initSingleton(
            string $id     = '',
            bool   $cached = true
        ) {
            $id    = \Ada\Core\Type::set($id);
            $class = get_called_class();
            if ($id === '') {
                $class_insts = self::$instsinsts ?? [];
                reset($class_insts);
                $id          = key($class_insts);
            }
            if ($cached && isset(self::$insts[$id])) {
                return self::$insts[$id];
            }
            $res = new $class(
                ...array_slice(func_get_args(), 2)
            );
            $res->id                 = $id;
            return self::$insts[$id] = $res;
        }

        protected function __construct() {
            return static::__construct();
        }

        protected function __clone() {
            return static::__clone();
        }

        protected function getId() {
            return $this->id;
        }

    }
