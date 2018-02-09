<?php
    /**
    * @package   ada/core
    * @version   1.0.0 07.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Files extends Proto {

        public static function get(string $name, $default = []): array {
            $name = Clean::cmd($name);
            if (!isset($_FILES[$name])) {
                return (array) $default;
            }
            $files = [];
            foreach ($_FILES[$name] as $prop => $values) {
                foreach ($values as $k => $v) {
                    $files[$k][$prop] = $v;
                }
            }
            return array_map(
                function($el) {
                    return UploadedFile::init($el);
                },
                $files
            );
        }

    }
