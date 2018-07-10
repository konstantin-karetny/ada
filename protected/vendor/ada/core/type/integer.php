<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Type;

    class Integer extends Type {

        const
            NUMBERS = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        public static function init(int $integer = 0): \Ada\Core\Type\Integer {
            return new static($integer);
        }

        public function getInteger(): int {
            return $this->getSubject();
        }

    }
