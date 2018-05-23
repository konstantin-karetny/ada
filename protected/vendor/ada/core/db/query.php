<?php
    /**
    * @package   project/core
    * @version   1.0.0 23.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Query extends \Ada\Core\Proto {

        const
            OPERANDS    = [
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
            $columns    = [],
            $db         = null,
            $from       = [],
            $joins      = [],
            $order_by   = [],
            $self_joins = [],
            $type       = 'select',
            $wheres     = [];

        public static function init(\Ada\Core\Db\Driver $db): \Ada\Core\Db\Query {
            return new static(...func_get_args());
        }

        protected function __construct(\Ada\Core\Db\Driver $db) {
            $this->db = $db;
        }

        public function exec(): bool {
            return $this->driverExec(__FUNCTION__, func_get_args());
        }

        public function fetchCell(
            string $type    = 'auto',
            string $default = null
        ) {
            return $this->driverExec(__FUNCTION__, func_get_args());
        }

        public function fetchColumn(
            string $column  = '',
            string $key     = '',
            array  $default = []
        ): array {
            return $this->driverExec(__FUNCTION__, func_get_args());
        }

        public function fetchRow(
            int $fetch_style = null,
                $default     = null
        ) {
            return $this->driverExec(__FUNCTION__, func_get_args());
        }

        public function fetchRows(
            int    $fetch_style = null,
            string $key         = '',
            array  $default     = []
        ): array {
            return $this->driverExec(__FUNCTION__, func_get_args());
        }

        public function from(
            string $table_name,
            string $as         = '',
            bool   $add_prefix = true
        ): \Ada\Core\Db\Query {
            $this->from = get_defined_vars();
            return $this;
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

        public function getJoins(): array {
            return $this->joins;
        }

        public function getOrderBy(): array {
            return $this->order_by;
        }

        public function getSelfJoins(): array {
            return $this->self_joins;
        }

        public function getType(): string {
            return $this->type;
        }

        public function getWheres(): array {
            return $this->wheres;
        }

        public function join(
            string $table_name,
            string $as         = '',
            bool   $add_prefix = true
        ): \Ada\Core\Db\Query {
            $this->addJoin('', ...func_get_args());
            return $this;
        }

        public function leftJoin(
            string $table_name,
            string $as         = '',
            bool   $add_prefix = true
        ): \Ada\Core\Db\Query {
            $this->addJoin('LEFT', ...func_get_args());
            return $this;
        }

        public function on(
            string $column1,
            string $operand,
            string $column2
        ): \Ada\Core\Db\Query {
            if (!$this->joins) {
                return $this;
            }
            $operand       = $this->validateOperand($operand);
            $this->joins[] = array_merge(
                array_pop($this->joins),
                get_defined_vars()
            );
            return $this;
        }

        public function orderBy(
            array $columns,
            bool  $asc = true
        ): \Ada\Core\Db\Query {
            $this->order_by = get_defined_vars();
            return $this;
        }

        public function rightJoin(
            string $table_name,
            string $as         = '',
            bool   $add_prefix = true
        ): \Ada\Core\Db\Query {
            $this->addJoin('RIGHT', ...func_get_args());
            return $this;
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

        public function selfJoin(string $as): \Ada\Core\Db\Query {
            $this->self_joins[] = $as;
            return $this;
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
            bool   $or     = false,
            bool   $escape = true
        ): \Ada\Core\Db\Query {
            $operand        = $this->validateOperand($operand);
            $this->wheres[] = get_defined_vars();
            return $this;
        }

        public function whereNotNull(
            string $column,
            bool   $or = false
        ): \Ada\Core\Db\Query {
            $this->wheres[] = array_merge(
                get_defined_vars(),
                [
                    'operand' => 'IS NOT NULL'
                ]
            );
            return $this;
        }

        public function whereNull(
            string $column,
            bool   $or = false
        ): \Ada\Core\Db\Query {
            $this->wheres[] = array_merge(
                get_defined_vars(),
                [
                    'operand' => 'IS NULL'
                ]
            );
            return $this;
        }

        protected function addJoin(
            string $type,
            string $table_name,
            string $as         = '',
            bool   $add_prefix = true
        ): array {
            $this->joins[] = get_defined_vars();
            return end($this->joins);
        }

        protected function driverExec(string $method, array $arguments) {
            return $this->getDb()->$method($this->toString(), ...$arguments);
        }

        protected function getQueryInsert(): string {

        }

        protected function getQuerySelect(): string {
            $db   = $this->getDb();
            $res  = 'SELECT ';
            foreach ($this->getColumns() as $column) {
                $res .= (
                    (
                        $column['name'] == '*'
                            ? $column['name']
                            : $db->q($column['name'], $column['as'])
                    ) .
                    ','
                );
            }
            $res  = rtrim($res, ', ');
            $from = $this->getFrom();
            $res .= ' FROM ' . (
                $db->{$from['add_prefix'] ? 't' : 'q'}(
                    $from['table_name'],
                    $from['as']
                )
            );
            foreach ($this->getSelfJoins() as $as) {
                $res .= ', ' . (
                    $db->{$from['add_prefix'] ? 't' : 'q'}(
                        $from['table_name'],
                        $as
                    )
                );
            }
            foreach ($this->getJoins() as $join) {
                $res .= (
                    ' ' . (!$join['type'] ? '' : $join['type'] . ' ') . 'JOIN ' .
                    $db->{$join['add_prefix'] ? 't' : 'q'}(
                        $join['table_name'],
                        $join['as']
                    ) .
                    (
                        isset($join['column1'])
                            ? (
                                ' ON ' .
                                $db->q($join['column1']) . ' ' .
                                $join['operand'] . ' ' .
                                $db->q($join['column2'])
                            )
                            : ''
                    )
                );
            }
            $wheres = $this->getWheres();
            if (!$wheres || $wheres[0]['or'] ?? false) {
                $res .= ' WHERE TRUE';
            }
            $i = 0;
            foreach ($wheres as $where) {
                $res .= (
                    ' ' .
                    ($where['or'] ? 'OR' : ($i ? 'AND' : 'WHERE')) . ' ' .
                    $db->q($where['column']) . ' ' .
                    (
                        in_array($where['operand'], ['IS NOT NULL', 'IS NULL'])
                            ? $where['operand']
                            : (
                                $where['operand'] . ' ' .
                                (
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
                                        : $db->{$where['escape'] ? 'e' : 'q'}(
                                            \Ada\Core\Type::set(
                                                $where['value'],
                                                'string'
                                            )
                                        )
                                )
                            )
                    )
                );
                $i++;
            }
            $order_by = $this->getOrderBy();
            if ($order_by) {
                $res .= ' ORDER BY';
                foreach ($order_by['columns'] as $column) {
                    $res .= ' ' . $db->q($column) . ',';
                }
                $res  = rtrim($res, ', ');
                $res .= $order_by['asc'] ? ' ASC' : ' DESC';
            }
            var_dump( $this, $res );
            return preg_replace('/\s+/', ' ', $res);
        }

        protected function getQueryUpdate(): string {

        }

        protected function validateOperand(string $operand): string {
            $operand = strtoupper(trim(preg_replace('/\s+/', ' ', $operand)));
            if (!in_array($operand, static::OPERANDS)) {
                throw new \Ada\Core\Exception(
                    'Unknown operand \'' . $operand . '\'',
                    1
                );
            }
            return $operand;
        }

    }
