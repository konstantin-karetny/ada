<?php
    /**
    * @package   ada/core
    * @version   1.0.0 23.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class DateTime extends \DateTime {

        public static function init(bool $cached = true): self {
            static $res;
            return $res && $cached ? $res : ($res = new self);
        }

    }
