<?php
    /**
    * @package   ada/core
    * @version   1.0.0 17.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Db extends Proto {

        const
            DEFAULT_PARAMS = [
                'attributes'  => [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ],
                'charset'     => 'utf8mb4',
                'collation'   => 'utf8mb4_unicode_ci',
                'date_format' => 'Y-m-d H:i:s',
                'driver'      => 'mysql',
                'host'        => '127.0.0.1',
                'name'        => '',
                'password'    => '',
                'prefix'     => '',
                'user'       => 'root'
            ];

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
            string $id     = '',
            array  $params = []
        ): Db\Driver {
            $params = array_merge(static::DEFAULT_PARAMS, $params);
            $class  = __CLASS__ . '\Drivers\\' . $params['driver'] . '\Driver';
            return $class::init(...func_get_args());
        }

    }
