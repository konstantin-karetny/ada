<?php
    /**
    * @package   project/core
    * @version   1.0.0 06.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Type;

    class Arr extends Type {

        protected
            $subj = [];

        public static function init(array $array = []): \Ada\Core\Type\Arr {
            return new static(...func_get_args());
        }

        public function diffRecursive(array $array): array {
            $res = [];
            foreach ($this->getSubj() as $k => $v) {
                if (array_key_exists($k, $array)) {
                    if (is_array($v)) {
                        $v_diffs = static::init($v)->diffRecursive($array[$k]);
                        if ($v_diffs) {
                            $res[$k] = $v_diffs;
                        }
                    } else {
                        if ($v != $array[$k]) {
                            $res[$k] = $v;
                        }
                    }
                } else {
                    $res[$k] = $v;
                }
            }
            return $res;
        }

        public function getInitialValue(): array {
            return parent::getInitialValue();
        }

        public function getSubj(): array {
            return parent::getSubj();
        }

        public function keysExist(array $keys): bool {
            return !array_diff_key(array_flip($keys), $this->getSubj());
        }

        public function mergeRecursive(array $array): array {
            $res = $this->getSubj();
            foreach ($array as $key => &$value) {
                if (
                    is_array($value)  &&
                    isset($res[$key]) &&
                    is_array($res[$key])
                ) {
                    $res[$key] = static::init($res[$key])->mergeRecursive($value);
                }
                else {
                    $res[$key] = $value;
                }
            }
            return $res;
        }

    }
