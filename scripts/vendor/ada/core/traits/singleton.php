<?php
    /**
    * @package   ada/core
    * @version   1.0.0 19.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Traits;

    trait Singleton {

        protected static
            $insts = [];

        protected
            $id    = '';

        public static function initSingleton(
            string $id     = '',
            bool   $cached = true
        ) {
            $id = \Ada\Core\Type::set($id);
            if ($cached && isset(static::$insts[$id])) {
                return static::$insts[$id];
            }
            $res = new static(...array_slice(func_get_args(), 2));
            if (!$res->getId()) {
                if ($id) {
                    throw new \Ada\Core\Exception('
                        No instance of class ' . get_called_class() . '
                        with identifier \'' . $id . '\''
                    );
                }
                return $res;
            }
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
