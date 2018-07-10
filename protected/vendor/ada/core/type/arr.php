<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Type;

    class Arr extends Type {

        protected
            $subj = [];

        public static function init(array $array = []): \Ada\Core\Type\Arr {
            return new static($array);
        }

        public function arsort(int $sort_flags = SORT_REGULAR): array {
            $res = $this->getArray();
            arsort($res, $sort_flags);
            return $res;
        }

        public function asort(int $sort_flags = SORT_REGULAR): array {
            $res = $this->getArray();
            asort($res, $sort_flags);
            return $res;
        }

        public function diffRecursive(array $array): array {
            $res = [];
            foreach ($this->getArray() as $k => $v) {
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

        public function first() {
            $subj = $this->getArray();
            return reset($subj);
        }

        public function firstKey() {
            $subj = $this->getArray();
            reset($subj);
            return \Ada\Core\Types::set(
                (string) key($subj)
            );
        }

        public function getArray(): array {
            return $this->getSubject();
        }

        public function getInitialValue(): array {
            return parent::getInitialValue();
        }

        public function keysExist(array $keys): bool {
            return !array_diff_key(array_flip($keys), $this->getArray());
        }

        public function krsort(int $sort_flags = SORT_REGULAR): array {
            $res = $this->getArray();
            krsort($res, $sort_flags);
            return $res;
        }

        public function ksort(int $sort_flags = SORT_REGULAR): array {
            $res = $this->getArray();
            ksort($res, $sort_flags);
            return $res;
        }

        public function last() {
            $subj = $this->getArray();
            return end($subj);
        }

        public function lastKey() {
            $subj = $this->getArray();
            end($subj);
            return \Ada\Core\Types::set(
                (string) key($subj)
            );
        }

        public function mergeRecursive(array $array): array {
            $res = $this->getArray();
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

        public function natcasesort(): array {
            $res = $this->getArray();
            natcasesort($res);
            return $res;
        }

        public function natsort(): array {
            $res = $this->getArray();
            natsort($res);
            return $res;
        }

        public function rsort(int $sort_flags = SORT_REGULAR): array {
            $res = $this->getArray();
            rsort($res, $sort_flags);
            return $res;
        }

        public function sort(int $sort_flags = SORT_REGULAR): array {
            $res = $this->getArray();
            sort($res, $sort_flags);
            return $res;
        }

        public function uasort(callable $value_compare_func): array {
            $res = $this->getArray();
            uasort($res, $value_compare_func);
            return $res;
        }

        public function uksort(callable $key_compare_func): array {
            $res = $this->getArray();
            uksort($res, $key_compare_func);
            return $res;
        }

        public function usort(callable $value_compare_func): array {
            $res = $this->getArray();
            usort($res, $value_compare_func);
            return $res;
        }

    }
