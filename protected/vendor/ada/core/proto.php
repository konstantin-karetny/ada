<?php
    /**
    * @package   project/core
    * @version   1.0.0 30.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    abstract class Proto {

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
