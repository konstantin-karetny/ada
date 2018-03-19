<?php
    /**
    * @package   ada/core
    * @version   1.0.0 19.01.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    abstract class Proto {

        const
            PRESETS = [];

        public static function getPresets(): array {
            return array_intersect_key(
                get_class_vars(get_called_class()),
                array_flip(static::PRESETS)
            );
        }

    }
