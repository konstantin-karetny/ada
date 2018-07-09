<?php
    /**
    * @package   project/core
    * @version   1.0.0 09.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Dev;

    class Constructor extends \Ada\Core\Proto {

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
            $last_k     = \Ada\Core\Type\Arr::init($props)->lastKey();
            echo '
                    protected';
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
                    public function get' . \Ada\Core\Type\Str::init($k)->toCamelCase() . '(): ' . \Ada\Core\Types::get($v) . ' {
                        return $this->' . $k . ';
                    }
                ';
            }
        }

        public static function renderSetters(array $props) {
            foreach ($props as $k => $v) {
                echo '
                    public function set' . \Ada\Core\Type\Str::init($k)->toCamelCase() . '(' . \Ada\Core\Types::get($v) . ' $' . $k . ') {
                        $this->' . $k . ' = $' . $k . ';
                    }
                ';
            }
        }

    }
