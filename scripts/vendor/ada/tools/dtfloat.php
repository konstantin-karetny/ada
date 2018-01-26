<?php
    /**
    * @package   ada/tools
    * @version   1.0.0 26.01.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Tools;

    class DtFloat extends DataType {

        protected static
            $types   = [
                'float',
                'double'
            ],
            $default = 0;

        public static function clean($var): float {
            return 0;
        }

    }
