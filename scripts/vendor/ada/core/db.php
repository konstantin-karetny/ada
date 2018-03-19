<?php
    /**
    * @package   ada/core
    * @version   1.0.0 19.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Db extends Proto {

        public static function getDrivers(bool $supported_only = false): array {
            $res = array_map('strtolower', (array) \PDO::getAvailableDrivers());
            sort($res);
            if (!$supported_only) {
                return $res;
            }
            foreach ($res as $i => $driver) {
                if (
                    !class_exists(__CLASS__ . '\Drivers\\' . $driver . '\Driver')
                ) {
                    unset($res[$i]);
                }
            }
            return $res;
        }

        public static function init(
            int   $id      = 0,
            array $presets = []
        ): Db\Driver {
            $class = (
                __CLASS__ . '\Drivers\\' .
                ($presets['driver'] ?? Db\Driver::getPresets()['driver']) .
                '\Driver'
            );
            return $class::init(...func_get_args());
        }

    }
