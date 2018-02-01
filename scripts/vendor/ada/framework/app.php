<?php
    /**
    * @package   ada/framework
    * @version   1.0.0 31.01.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Framework;

    class App extends \Ada\Core\Singleton {

        protected static
            $interfaces = [
                'front'
            ];

        public static function init(
            string $interface = '',
            array  $params    = [],
            bool   $cached    = true
        ): self {
            return parent::init(
                $interface ? $interface : reset(self::$interfaces),
                $params,
                $cached
            );
        }

        public function getInterfaces(): array {
            return self::$interfaces;
        }

        public function addInterface(string $interface) {
            return array_push(self::$interfaces, $interface);
        }

        public function setInterfaces(array $interfaces) {
            self::$interfaces = \Ada\Core\Type::set($interfaces, 'string', true);
        }

    }
