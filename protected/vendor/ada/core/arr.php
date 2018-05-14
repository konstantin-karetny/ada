<?php
    /**
    * @package   project/core
    * @version   1.0.0 14.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Arr extends Proto {

        protected
            $array = [];

        public static function init(array $array = []): \Ada\Core\Arr {
            return new static(...func_get_args());
        }

        public function __construct(array $array = []) {
            $this->array = $array;
        }

        public function diffRecursive(array $array): array {
            $res = [];
            foreach ($this->toArray() as $k => $v) {
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

        public function keysExist(array $keys): bool {
            return !array_diff_key(array_flip($keys), $this->toArray());
        }

        public function mergeRecursive(array $array): array {
            $res = $this->toArray();
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

        public function toArray(): array {
            return $this->array;
        }

    }
