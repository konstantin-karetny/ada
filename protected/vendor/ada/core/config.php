<?php
    /**
    * @package   project/core
    * @version   1.0.0 26.10.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Config extends Proto {

        protected static
            $default_id = '',
            $instances  = [];

        protected
            $params     = [];

        public static function add(string $id, array $params): bool {
            $id = Clean::cmd($id);
            if (isset(static::$instances[$id])) {
                throw new Exception(
                    '\'' . $id . '\' identifier is already in use',
                    1
                );
            }
            static::$instances[$id] = new static($params);
            return true;
        }

        public static function getDefaultId(): string {
            return static::$default_id;
        }

        public static function init(string $id = null): \Ada\Core\Config {
            $id = $id === null ? static::getDefaultId() : Clean::cmd($id);
            if (!isset(static::$instances[$id])) {
                throw new Exception(
                    'No instance added with identifier \'' . $id . '\'',
                    2
                );
            }
            return static::$instances[$id];
        }

        public static function preset(array $params): bool {
            if (static::$instances) {
                return false;
            }
            foreach ($params as $k => $v) {
                $k = Clean::cmd($k);
                switch ($k) {
                    case 'default_id':
                        static::$$k = Clean::cmd($v);
                        break;
                }
            }
            return true;
        }

        public function __construct(array $params) {
            $this->params = Types::set($params);
        }

        public function param(string $param) {
            if (!isset($this->params[$param])) {
                throw new Exception(
                    'Unknown parameter \'' . $param . '\'',
                    3
                );
            }
            return $this->params[$param];
        }

        public function params(): array {
            return $this->params;
        }

    }
