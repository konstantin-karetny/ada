<?php
    /**
    * @package   ada/lib
    * @version   1.0.0 02.10.2017
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Lib;

    class Config extends Singleton {

        private $data = [];

        protected function __construct(array $options = []) {
            if (empty($options['filenames'])) return;

            if (!is_array($options['filenames'])) {
                $options['filenames'] = [$options['filenames']];
            }
            foreach ($options['filenames'] as $filename) {
                $this->data = array_merge($this->data, File::parseIni($filename));
            }
        }

        static public function get($key, $default = null) {
            $self = self::getinst();
            return isset($self->data[$key]) ? $self->data[$key] : $default;
        }

        /** @return Config */ public static function getInst($params = []) { return parent::getInst($params); }

    }
