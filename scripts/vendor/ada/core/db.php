<?php
    /**
    * @package   ada/core
    * @version   1.0.0 14.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Db extends Proto {

        const
            DEFAULT_PARAMS = [
                'charset'    => 'utf8',
                'driver'     => 'mysql',
                'host'       => '127.0.0.1',
                'name'       => '',
                'password'   => '',
                'pdo_params' => [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ],
                'prefix'     => '',
                'user'       => 'root'
            ];

        public static function init(
            string $id     = '',
            array  $params = []
        ): Db\Drivers\Driver {
            $params = array_merge(static::DEFAULT_PARAMS, $params);
            return call_user_func_array(
                __CLASS__ . '\Drivers\\' . $params['driver'] . '::init',
                func_get_args()
            );
        }

        public static function getDrivers(bool $supported_only = false): array {
            $res = array_map('strtolower', (array) \PDO::getAvailableDrivers());
            sort($res);
            if (!$supported_only) {
                return $res;
            }
            foreach ($res as $i => $driver) {
                if (!class_exists(__CLASS__ . '\Drivers\\' . $driver)) {
                    unset($res[$i]);
                }
            }
            return $res;
        }

    }
