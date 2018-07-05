<?php
    /**
    * @package   project/core
    * @version   1.0.0 05.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Str extends Proto {

        public static function hash(
            string $string,
            string $algo = 'sha1'
        ): string {
            $hash  = hash($algo, $string);
            $start = strlen($hash) / 10;
            return md5(substr($hash, $start, $start * 8));
        }

        public static function separateWith(
            string $string,
            string $separator = ' '
        ): string {
            return (string) preg_replace(
                '/[ \-_]+/',
                $separator,
                trim(preg_replace('/([A-Z])/', ' $1', $string))
            );
        }

        public static function toCamelCase(
            string $string,
            bool   $ucfirst = true
        ): string {
            $res = (string) str_replace(
                ' ',
                '',
                ucwords(static::separateWith($string))
            );
            return $ucfirst ? $res : lcfirst($res);
        }

        public static function toOneLine(
            string $string,
            bool $trim = true
        ): string {
            $res = preg_replace('/\s+/', ' ', $string);
            return $trim ? trim($res) : $res;
        }

    }
