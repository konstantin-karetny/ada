<?php
    /**
    * @package   project/core
    * @version   1.0.0 09.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Inp;

    class Post extends Inp {

        public static function get(
            string $name,
            string $filter,
                   $default = ''
        ) {
            return \Ada\Core\Clean::value(
                static::getStorage()[\Ada\Core\Clean::cmd($name)] ?? $default,
                $filter
            );
        }

        public static function getArray(
            string $name,
            string $filter  = '',
            array  $default = []
        ): array {
            if (in_array($filter, \Ada\Core\Types::NAMES['object'])) {
                throw new \Ada\Core\Exception(
                    '
                        Wrong filter \'' . $filter . '\'. ' .
                        static::getStorageName() . ' array can not contain objects
                    ',
                    1
                );
            }
            $res = \Ada\Core\Types::set(
                static::getStorage()[\Ada\Core\Clean::cmd($name)] ?? $default
            );
            return $filter ? \Ada\Core\Clean::values($res, $filter) : $res;
        }

    }
