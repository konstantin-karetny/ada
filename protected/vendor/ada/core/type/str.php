<?php
    /**
    * @package   project/core
    * @version   1.0.0 06.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Type;

    class Str extends Type {

        protected
            $subj = '';

        public static function init(string $string = ''): \Ada\Core\Type\Str {
            return new static(...func_get_args());
        }

        public function getInitialValue(): string {
            return parent::getInitialValue();
        }

        public function getSubj(): string {
            return parent::getSubj();
        }

        public function hash(
            string $algo1     = 'sha1',
            string $algo2     = 'md5',
            int    $cut_index = 8
        ): string {
            $hash  = hash($algo1, $this->getSubj());
            $start = strlen($hash) / 10;
            return hash(
                $algo2,
                substr(
                    $hash,
                    $start,
                    $start * substr($cut_index, 0, 1)
                )
            );
        }

        public function oneLine(bool $trim = true): string {
            $res = preg_replace('/\s+/', ' ', $this->getSubj());
            return $trim ? trim($res) : $res;
        }

        public function separate(
            string $separator   = ' ',
            string $replacement = ' \-_'
        ): string {
            return (string) preg_replace(
                '/[' . $replacement . ']+/',
                $separator,
                trim(
                    preg_replace('/([A-Z])/', ' $1', $this->getSubj())
                )
            );
        }

        public function toCamelCase(bool $ucfirst = true): string {
            $res = (string) str_replace(' ', '', ucwords($this->separate()));
            return $ucfirst ? $res : lcfirst($res);
        }

    }
