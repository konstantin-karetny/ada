<?php
    /**
    * @package   project/cms
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\HMVC;

    class Controller extends \Ada\Core\Proto {

        public static function init(): \Ada\Core\HMVC\Controller {
            return new static();
        }

        protected function __construct() {
            
        }

    }
