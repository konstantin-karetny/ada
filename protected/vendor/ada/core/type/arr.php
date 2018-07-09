<?php
    /**
    * @package   project/core
    * @version   1.0.0 09.07.2018
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
            $res = $this->getSubj();
            arsort($res, $sort_flags);
            return $res;
        }

        public function asort(int $sort_flags = SORT_REGULAR): array {
            $res = $this->getSubj();
            asort($res, $sort_flags);
            return $res;
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

        public function first() {
            $subj = $this->getSubj();
            return reset($subj);
        }

        public function firstKey() {
            $subj = $this->getSubj();
            reset($subj);
            return \Ada\Core\Types::set(
                (string) key($subj)
            );
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

        public function krsort(int $sort_flags = SORT_REGULAR): array {
            $res = $this->getSubj();
            krsort($res, $sort_flags);
            return $res;
        }

        public function ksort(int $sort_flags = SORT_REGULAR): array {
            $res = $this->getSubj();
            ksort($res, $sort_flags);
            return $res;
        }

        public function last() {
            $subj = $this->getSubj();
            return end($subj);
        }

        public function lastKey() {
            $subj = $this->getSubj();
            end($subj);
            return \Ada\Core\Types::set(
                (string) key($subj)
            );
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

        public function natcasesort(): array {
            $res = $this->getSubj();
            natcasesort($res);
            return $res;
        }

        public function natsort(): array {
            $res = $this->getSubj();
            natsort($res);
            return $res;
        }

        public function rsort(int $sort_flags = SORT_REGULAR): array {
            $res = $this->getSubj();
            rsort($res, $sort_flags);
            return $res;
        }

        public function sort(int $sort_flags = SORT_REGULAR): array {
            $res = $this->getSubj();
            sort($res, $sort_flags);
            return $res;
        }

        public function uasort(callable $value_compare_func): array {
            $res = $this->getSubj();
            uasort($res, $value_compare_func);
            return $res;
        }

        public function uksort(callable $key_compare_func): array {
            $res = $this->getSubj();
            uksort($res, $key_compare_func);
            return $res;
        }

        public function usort(callable $value_compare_func): array {
            $res = $this->getSubj();
            usort($res, $value_compare_func);
            return $res;
        }

    }
