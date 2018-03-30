<?php
    /**
    * @package   project/core
    * @version   1.0.0 30.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Arr extends Proto {

        public static function keysExist(array $array, array $keys): bool {
            return !array_diff_key(array_flip($keys), $array);
        }

    }
