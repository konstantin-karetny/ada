<?php
    /**
    * @package   project/core
    * @version   1.0.0 19.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    abstract class Proto {

        protected function drop() {
            foreach ($this as $k => $v) {
                $this->$k = Type::INITIAL_VALUES[Type::get($v)];
            }
        }

        protected function getProps(array $except = []): array {
            $res = [];
            foreach ($this as $k => $v) {
                if (in_array($k, $except)) {
                    continue;
                }
                $getter  = 'get' . \Ada\Core\Str::toCamelCase($k);
                $res[$k] = (
                    method_exists($this, $getter)
                        ? $this->$getter()
                        : Type::set($v)
                );
            }
            return $res;
        }

        protected function setProps(array $props) {
            foreach ($props as $k => $v) {
                if (!property_exists($this, $k)) {
                    continue;
                }
                $setter = 'set' . \Ada\Core\Str::toCamelCase($k);
                if (method_exists($this, $setter)) {
                    $this->$setter($v);
                    continue;
                }
                $this->$k = \Ada\Core\Type::set($v);
            }
        }

    }
