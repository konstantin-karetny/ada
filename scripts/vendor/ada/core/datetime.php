<?php
    /**
    * @package   ada/core
    * @version   1.0.0 26.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class DateTime extends \DateTime {

        public static function init(
            string $time        = 'now',
            string $timezone_id = ''
        ): self {
            return new self(
                $time,
                $timezone_id ? DateTimeZone::init($timezone_id) : null
            );
        }

        public function format($format) {
            return parent::format($format);
        }

    }
