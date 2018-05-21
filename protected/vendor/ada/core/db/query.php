<?php
    /**
    * @package   project/core
    * @version   1.0.0 21.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Query extends \Ada\Core\Proto {

        protected
            $columns = [],
            $db      = null,
            $from    = [],
            $ors     = [],
            $wheres  = [];

        public static function init(\Ada\Core\Db\Driver $db): \Ada\Core\Db\Query {
            return new static(...func_get_args());
        }

        protected function __construct(\Ada\Core\Db\Driver $db) {
            $this->db = $db;
        }

        public function select(array $columns = ['*']): \Ada\Core\Db\Query {
            $this->columns = $columns;
            return $this;
        }

        public function from(
            string $table_name,
            string $as = ''
        ): \Ada\Core\Db\Query {
            $this->from = [
                $table_name
            ];
            if ($as !== '') {
                array_push($this->from, $as);
            }
            return $this;
        }

        public function or(
            string $column,
            string $operand,
            mixed  $value
        ): \Ada\Core\Db\Query {
            $this->ors[] = [
                $column,
                $operand,
                $value
            ];
            return $this;
        }

        public function where(
            string $column,
            string $operand,
            mixed  $value
        ): \Ada\Core\Db\Query {
            $this->wheres[] = [
                $column,
                $operand,
                $value
            ];
            return $this;
        }

    }
