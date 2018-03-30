<?php
    /**
    * @package   project/core
    * @version   1.0.0 29.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Obj extends Proto {

        public static function getBasename($object): string {
            return basename(str_replace('\\', '/', get_class($object)));
        }

    }
