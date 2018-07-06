<?php
    /**
    * @package   project/core
    * @version   1.0.0 06.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\DateTime;

    class TimeZone extends \DateTimeZone {

        public static function init(
            string $timezone_name
        ): \Ada\Core\DateTime\TimeZone {
            return new static($timezone_name);
        }

        public function __construct(string $timezone_name) {
            try {
                parent::__construct($timezone_name);
            } catch (\Throwable $e) {
                throw new Exception(
                    'Wrong time zone \'' . $timezone_name . '\'. ' . $e->getMessage(),
                    1
                );
            }
        }

    }
