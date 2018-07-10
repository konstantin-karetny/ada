<?php
    /**
    * @package   project/core
    * @version   1.0.0 06.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Type;

    abstract class Type extends \Ada\Core\Proto {

        protected
            $subj;

        protected function __construct($subj) {
            $this->subj = $subj;
        }

        protected function getSubject() {
            return $this->subj;
        }

        public function getInitialValue() {
            return \Ada\Core\Types::INITIAL_VALUES[$this->getType()];
        }

        public function getType(): string {
            return \Ada\Core\Types::get($this->getSubject());
        }

    }
