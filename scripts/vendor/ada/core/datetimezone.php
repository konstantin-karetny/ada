<?php
    /**
    * @package   ada/core
    * @version   1.0.0 16.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class DateTimeZone extends \DateTimeZone {

        public static function init(string $timezone_id): self {
            try {
                return new static($timezone_id);
            } catch (\Throwable $e) {
                throw new Exception(
                    'Failed to set time zone \'' . $timezone_id . '\'. ' . $e->getMessage(),
                    1
                );
            }
        }

    }
