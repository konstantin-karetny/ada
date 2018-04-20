<?php
    /**
    * @package   project/core
    * @version   1.0.0 20.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Arr extends Proto {

        protected
            $array = [];

        public static function init(array $array = []): self {
            return new static(...func_get_args());
        }

        public function __construct(array $array = []) {
            $this->array = $array;
        }

        public function diffRecursive(array $array2): array {
            $res = [];
            foreach ($this->toArray() as $k => $v) {
                if (array_key_exists($k, $array2)) {
                    if (is_array($v)) {
                        $v_diffs = static::init($v)->diffRecursive($array2[$k]);
                        if ($v_diffs) {
                            $res[$k] = $v_diffs;
                        }
                    } else {
                        if ($v != $array2[$k]) {
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

        public function toArray(): array {
            return $this->array;
        }

    }
