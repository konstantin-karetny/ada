<?php
    /**
    * @package   ada/core
    * @version   1.0.0 09.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Db extends Proto {

        public static function init(
            string $id     = '',
            bool   $cached = true,
                ...$args
        ): Db\Drivers\Driver {
            $params = reset($args) + [

            ];
            $driver = (
                __NAMESPACE__ .
                '\\Db\\Drivers\\' .
                ucfirst($params['driver'] ?? Db\Drivers\Driver::init()->getDriver())
            );
            $driver           = $driver::init($id, $cached);
            foreach ($params as $k => $v) {
                $driver->{'set' . Strings::toCamelCase($k)}($v);
            }
            return $driver;
        }

    }
