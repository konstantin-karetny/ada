<?php
    /**
    * @package   ada/core
    * @version   1.0.0 26.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class DateTimeZone extends \DateTimeZone {

        public static function init(string $timezone_id): self {
            return new self($timezone_id);
        }

    }
