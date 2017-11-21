<?php
    /**
    * @package    package_lib
    * @version    1.0.0 11.10.2016
    * @copyright  copyright
    * @license    Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Lib;

    class File {

        public static function isReadable(string $path) {
            if (!file_exists($path) || !is_file($path)) return false;

            if (!is_readable($path)) {
                @chmod($path, 0777);
            }
            if (!is_readable($path)) return false;

            return true;
        }

        public static function getExt(string $filename) {
            return substr($filename, strrpos($filename, '.') + 1);
        }

        public static function stripExt(string  $filename) {
            return preg_replace('#\.[^.]*$#', '', $filename);
        }

        public static function parseIni(string  $filename, bool $process_sections = true) {
            $res = parse_ini_file($filename, $process_sections);
            return $res ? $res : [];
        }

    }
