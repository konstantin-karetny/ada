<?php
    /**
    * @package   project/core
    * @version   1.0.0 07.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Constructor extends Proto {

        public static function renderClass(
            array $props,
            bool  $render_props   = true,
            bool  $render_getters = true,
            bool  $render_setters = true
        ) {
            if ($render_props) {
                self::renderProps($props);
                echo "\n";
            }
            if ($render_getters) {
                self::renderGetters($props);
            }
            if ($render_setters) {
                self::renderSetters($props);
            }
        }

        public static function renderProps(array $props) {
            $pad_length = max(array_map('strlen', array_keys($props)));
            $last_k     = end(array_keys($props));
            foreach ($props as $k => $v) {
                echo '
                    $' . str_pad($k, $pad_length) . ' = ' .
                    (is_string($v) ? ('\'' . $v . '\'') : $v) .
                    ($k == $last_k ?  ';' : ',');
            }
        }

        public static function renderGetters(array $props) {
            foreach ($props as $k => $v) {
                echo '
                    public function get' . Strings::toCamelCase($k) . '(): ' . Type::get($v) . ' {
                        return $this->' . $k . ';
                    }
                ';
            }
        }

        public static function renderSetters(array $props) {
            foreach ($props as $k => $v) {
                echo '
                    public function set' . Strings::toCamelCase($k) . '(' . Type::get($v) . ' $' . $k . '): ' . Type::get($v) . ' {
                        $this->' . $k . ' = $' . $k . ';
                    }
                ';
            }
        }

    }
