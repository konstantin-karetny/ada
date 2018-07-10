<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Type;

    class Obj extends Type {

        public static function init($object): \Ada\Core\Type\Obj {
            if (!is_object($object)) {
                throw new \Ada\Core\Exception(
                    '
                        Argument 1 passed to ' . __METHOD__ . '()
                        must be of the type object, ' .
                        \Ada\Core\Types::get($object) . ' given
                    ',
                    1
                );
            }
            return new static($object);
        }

        public function getBasename(): string {
            return basename(
                str_replace(
                    '\\',
                    '/',
                    get_class($this->getObject())
                )
            );
        }

        public function getObject() {
            return $this->getSubject();
        }

    }
