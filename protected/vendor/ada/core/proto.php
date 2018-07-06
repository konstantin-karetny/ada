<?php
    /**
    * @package   project/core
    * @version   1.0.0 06.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    abstract class Proto {

        protected function dropProps() {
            foreach ($this as $k => $v) {
                $this->$k = Types::INITIAL_VALUES[Types::get($v)];
            }
        }

        protected function getProps(array $except = []): array {
            $res = [];
            foreach ($this as $k => $v) {
                if (in_array($k, $except)) {
                    continue;
                }
                $getter  = 'get' . Type\Str::init($k)->toCamelCase();
                $res[$k] = (
                    method_exists($this, $getter)
                        ? $this->$getter()
                        : Types::set($v)
                );
            }
            return $res;
        }

        protected function setProps(array $props) {
            foreach ($props as $k => $v) {
                if (!property_exists($this, $k)) {
                    continue;
                }
                $setter = 'set' . Type\Str::init($k)->toCamelCase();
                if (method_exists($this, $setter)) {
                    $this->$setter($v);
                    continue;
                }
                $this->$k = \Ada\Core\Types::set($v);
            }
        }

    }
