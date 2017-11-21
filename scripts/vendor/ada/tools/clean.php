<?php
    /**
    * @package   ada/tools
    * @version   1.0.0 09.11.2017
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Tools;

    class Clean extends Proto {

        public static function uri(string $raw): string {
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
            return trim($res);
        }

    }
