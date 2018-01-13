<?php
    /**
    * @package   ada/tools
    * @version   1.0.0 13.01.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Tools;

    class Clean extends \Ada\Core\Proto {

        public static function url(string $raw): string {
            $res = filter_var(
                str_replace(
                    [
                        '\'',
                        '"',
                        '<',
                        '>'
                    ],
                    [
                        '%27',
                        '%22',
                        '%3C',
                        '%3E'
                    ],
                    $raw
                ),
                FILTER_SANITIZE_URL
            );
            if ($res === false) {
                throw new \ErrorException;
            }
            return trim(rtrim($res, '/'));
        }

    }
