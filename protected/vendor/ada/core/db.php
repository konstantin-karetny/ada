<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Db extends Proto {

        const
            DEFAULT_DRIVER = 'mysql';

        protected static
            $instances     = [];

        public static function add(array $params): int {
            $class = implode(
                '\\',
                [
                    __CLASS__,
                    'Drivers',
                    $params['driver'] ?? static::DEFAULT_DRIVER,
                    'Driver'
                ]
            );
            static::$instances[] = $class::init($params);
            return Type\Arr::init(static::$instances)->lastKey();
        }

        public static function getDrivers(bool $supported_only = false): array {
            $res = Type\Arr::init(
                array_map(
                    'strtolower',
                    (array) \PDO::getAvailableDrivers()
                )
            )->sort();
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

        public static function init(int $id = 0): Db\Driver {
            if (!isset(static::$instances[$id])) {
                throw new Exception(
                    'No database added with identifier \'' . $id . '\'',
                    1
                );
            }
            return static::$instances[$id];
        }

    }
