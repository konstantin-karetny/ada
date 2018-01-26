<?php
    /**
    * @package   ada/tools
    * @version   1.0.0 26.01.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Tools;

    abstract class DataType extends \Ada\Core\Proto {

        protected static
            $types   = [],
            $default = null;

        abstract public static function clean($var);

        public static function getDefault() {
            return Type::set(static::$default, static::getType());
        }

        public static function getType(): string {
            return reset(static::getTypes());
        }

        public static function getTypes(): array {
            return static::$types;
        }

    }
