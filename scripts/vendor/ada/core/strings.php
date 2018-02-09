<?php
    /**
    * @package   ada/core
    * @version   1.0.0 07.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Strings extends Proto {

        public static function separateWith(
            string $val,
            string $separator = ' '
        ): string {
            return (string) preg_replace(
                '/[ \-_]+/',
                $separator,
                trim(preg_replace('/([A-Z])/', ' $1', $val))
            );
        }

        public static function toCamelCase(string $val): string {
            return (string) str_replace(
                ' ',
                '',
                ucwords(self::separateWith($val))
            );
        }

    }
