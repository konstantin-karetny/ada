<?php
    /**
    * @package   project/core
    * @version   1.0.0 09.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Inp;

    class Files extends \Ada\Core\Proto {

        public static function get(
            string $name,
            array  $default = []
        ): array {
            $name = \Ada\Core\Clean::cmd($name);
            if (!isset($_FILES[$name])) {
                return $default;
            }
            $res = [];
            foreach ($_FILES[$name] as $prop => $values) {
                foreach ($values as $k => $v) {
                    $res[$k][$prop] = $v;
                }
            }
            return array_map(
                function($el) {
                    return File::init($el);
                },
                $res
            );
        }

    }
