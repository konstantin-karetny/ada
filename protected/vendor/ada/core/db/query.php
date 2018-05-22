<?php
    /**
    * @package   project/core
    * @version   1.0.0 22.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Query extends \Ada\Core\Proto {

        const
            OPERANDS = [
                '!=',
                '*',
                '+',
                '-',
                '/',
                '<',
                '<=',
                '<>',
                '=',
                '>',
                '>=',
                'IN',
                'IS NOT NULL',
                'IS NULL',
                'LIKE',
                'NOT IN'
            ];

        protected
            $columns = [],
            $db      = null,
            $from    = [],
            $ors     = [],
            $raw     = '',
            $type    = 'select',
            $wheres  = [];

        public static function init(\Ada\Core\Db\Driver $db): \Ada\Core\Db\Query {
            return new static(...func_get_args());
        }

        protected function __construct(\Ada\Core\Db\Driver $db) {
            $this->db = $db;
        }

        public function getColumns(): array {
            return $this->columns;
        }

        public function getDb(): \Ada\Core\Db\Driver {
            return $this->db;
        }

        public function getFrom(): array {
            return $this->from;
        }

        public function getRaw(): string {
            return $this->raw;
        }

        public function getType(): string {
            return $this->type;
        }

        public function getWheres(): array {
            return $this->wheres;
        }

        public function select(array $columns = ['*']): \Ada\Core\Db\Query {
            $this->type = 'select';
            foreach ($columns as $column) {
                $this->columns[] = array_combine(
                    ['name', 'as'],
                    array_slice(
                        array_pad(
                            is_array($column)
                                ? $column
                                : \Ada\Core\Type::set($column, 'array'),
                            2,
                            ''
                        ),
                        0,
                        2
                    )
                );
            }
            return $this;
        }

        public function from(
            string $table_name,
            string $as         = '',
            bool   $add_prefix = true
        ): \Ada\Core\Db\Query {
            $this->from = get_defined_vars();
            return $this;
        }

        public function raw(string $query): \Ada\Core\Db\Query {

        }

        public function toString(): string {
            if ($this->getType() == 'select' && !$this->getFrom()) {
                throw new \Ada\Core\Exception(
                    '\'From\' statement is required',
                    1
                );
            }
            return $this->{'getQuery' . ucfirst($this->getType())}();
        }

        public function where(
            string $column,
            string $operand,
                   $value,
            bool   $or = false
        ): \Ada\Core\Db\Query {
            $where            = get_defined_vars();
            $where['operand'] = strtoupper(trim(
                preg_replace('/\s+/', ' ', $operand)
            ));
            if (!in_array($where['operand'], static::OPERANDS)) {
                throw new \Ada\Core\Exception(
                    'Unknown operand \'' . $operand . '\'',
                    1
                );
            }
            $this->wheres[] = $where;
            return $this;
        }

        protected function getQueryInsert(): string {

        }

        protected function getQuerySelect(): string {
            if ($this->getRaw()) {
                return $this->getRaw();
            }
            $db  = $this->getDb();
            $res = 'SELECT';
            foreach ($this->getColumns() as $column) {
                $res .= ' ' . $db->q($column['name'], $column['as']) . ',';
            }
            $res  = rtrim($res, ', ');
            $from = $this->getFrom();
            $res .= ' FROM ' . (
                $db->{$from['add_prefix'] ? 't' : 'q'}(
                    $from['table_name'],
                    $from['as']
                )
            );
            $i = 0;
            foreach ($this->getWheres() as $where) {
                $res .= (
                    ' ' . ($where['or'] ? 'OR' : $i ? 'AND' : 'WHERE') .
                    ' ' . $db->q($where['column']) .
                    ' ' . $where['operand'] .
                    ' ' . (
                        in_array($where['operand'], ['IN', 'NOT IN'])
                            ? (
                                '(' .
                                    implode(
                                        ', ',
                                        array_map(
                                           [$db, 'e'],
                                           is_array($where['value'])
                                                ? $where['value']
                                                : \Ada\Core\Type::set(
                                                    $where['value'],
                                                    'array'
                                                )
                                        )
                                    ) .
                                ')'
                            )
                            : $db->e(
                                \Ada\Core\Type::set($where['value'], 'string')
                            )
                    )
                );
                $i++;
            }
            return $res;
        }

        protected function getQueryUpdate(): string {

        }

    }
