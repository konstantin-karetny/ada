<?php
    /**
    * @package   ada/framework
    * @version   1.0.0 03.10.2017
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Framework;

    class App extends \Ada\Tools\Singleton {

        /**
         * @throws Exception
         */
        public static function getInst(string $id = '', bool $cached = true): App {

            /* TODO autofill $id according to interface */

            return parent::getInst($id, $cached);
        }


    }
