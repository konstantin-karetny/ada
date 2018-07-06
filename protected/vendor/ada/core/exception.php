<?php
    /**
    * @package   project/core
    * @version   1.0.0 06.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Exception extends \Exception {

        protected
            $context = '';

        public function __construct(
            string     $message  = '',
            int        $code     = 0,
            \Throwable $previous = null
        ) {
            $message       = Type\Str::init($message)->oneLine();
            parent::__construct($message, $code, $previous);
            $trace         = $this->getTrace();
            $this->context = reset($trace)['class'];
            $this->message = $this->context . ' error. ' . $this->message;
        }

        public function getContext(): string {
            return $this->context;
        }

	}

