<?php
    /**
    * @package   project/core
    * @version   1.0.0 07.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Hash extends Proto {

        public static function asMd5(string $line, string $algo = 'sha1') {
            $hash  = hash($algo, $line);
            $start = strlen($hash) / 10;
            return md5(substr($hash, $start, $start * 8));
        }

    }
