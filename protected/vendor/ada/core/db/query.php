<?php
    /**
    * @package   project/core
    * @version   1.0.0 13.06.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Query extends \Ada\Core\Proto {

        const
            OPERANDS       = [
                '!=',
                '<',
                '<=',
                '<>',
                '=',
                '>',
                '>=',
                'LIKE'
            ];

        protected
            $columns       = [],
            $db            = null,
            $distinct      = false,
            $from          = [],
            $function      = [],
            $group_by      = [],
            $joins         = [],
            $order_by      = [],
            $self_joins    = [],
            $type          = 'select',
            $union_queries = [],
            $wheres        = [];

        public static function init(\Ada\Core\Db\Driver $db): \Ada\Core\Db\Query {
            return new static(...func_get_args());
        }

        protected function __construct(\Ada\Core\Db\Driver $db) {
            $this->db = $db;
        }

        public function distinct(bool $distinct): \Ada\Core\Db\Query {
            $this->distinct = $distinct;
            return $this;
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
            string $alias      = '',
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

        public function getDistinct(): bool {
            return $this->distinct;
        }

        public function getFrom(): array {
            return $this->from;
        }

        public function getFunction(): array {
            return $this->function;
        }

        public function getGroupBy(): array {
            return $this->group_by;
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

        public function getUnionQueries(): array {
            return $this->union_queries;
        }

        public function getWheres(): array {
            return $this->wheres;
        }

        public function groupBy(array $columns): \Ada\Core\Db\Query {
            $this->group_by = array_map(
                function(string $column) {
                    return \Ada\Core\Clean::cmd($column);
                },
                $columns
            );
            return $this;
        }

        public function join(
            string $table_name,
            string $alias      = '',
            bool   $add_prefix = true
        ): \Ada\Core\Db\Query {
            $this->addJoin('', ...func_get_args());
            return $this;
        }

        public function leftJoin(
            string $table_name,
            string $alias      = '',
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

        public function orBetween(
            string $column,
            string $value1,
            string $value2
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'BETWEEN',
                (
                    $this->getDb()->e($value1) .
                    ' AND ' .
                    $this->getDb()->e($value2)
                ),
                true
            );
            return $this;
        }

        public function orColumn(
            string $column,
            string $operand,
            string $column2
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                $this->validateOperand($operand),
                $this->getDb()->q($column2),
                true
            );
            return $this;
        }

        public function orderBy(
            array $columns,
            bool  $asc = true
        ): \Ada\Core\Db\Query {
            $columns = array_map(
                function(string $column) {
                    return \Ada\Core\Clean::cmd($column);
                },
                $columns
            );
            $this->order_by = get_defined_vars();
            return $this;
        }

        public function orIn(
            string $column,
            array  $values
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'IN',
                (
                    '(' .
                        implode(', ', array_map([$this->getDb(), 'e'], $values)) .
                    ')'
                ),
                true
            );
            return $this;
        }

        public function orNotBetween(
            string $column,
            string $value1,
            string $value2
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'NOT BETWEEN',
                (
                    $this->getDb()->e($value1) .
                    ' AND ' .
                    $this->getDb()->e($value2)
                ),
                true
            );
            return $this;
        }

        public function orNotIn(
            string $column,
            array  $values
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'NOT IN',
                (
                    '(' .
                        implode(', ', array_map([$this->getDb(), 'e'], $values)) .
                    ')'
                ),
                true
            );
            return $this;
        }

        public function orNotNull(string $column): \Ada\Core\Db\Query {
            $this->addWhere($column, 'IS NOT NULL', '', true);
            return $this;
        }

        public function orNull(string $column): \Ada\Core\Db\Query {
            $this->addWhere($column, 'IS NULL', '', true);
            return $this;
        }

        public function orWhere(
            string $column,
            string $operand,
            string $value
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                $this->validateOperand($operand),
                $this->getDb()->e($value),
                true
            );
            return $this;
        }

        public function rightJoin(
            string $table_name,
            string $alias      = '',
            bool   $add_prefix = true
        ): \Ada\Core\Db\Query {
            $this->addJoin('RIGHT', ...func_get_args());
            return $this;
        }

        public function select(array $columns = ['*']): \Ada\Core\Db\Query {
            $this->type = 'select';
            foreach ($columns as $column) {
                $this->addColumn(
                    ...\Ada\Core\Type::set($column, 'array', false)
                );
            }
            return $this;
        }

        public function selectAvg(string $column): \Ada\Core\Db\Query {
            $this->setFunction('AVG', $column);
            return $this;
        }

        public function selectCount(string $column): \Ada\Core\Db\Query {
            $this->setFunction('COUNT', $column);
            return $this;
        }

        public function selectMax(string $column): \Ada\Core\Db\Query {
            $this->setFunction('MAX', $column);
            return $this;
        }

        public function selectMin(string $column): \Ada\Core\Db\Query {
            $this->setFunction('MIN', $column);
            return $this;
        }

        public function selectSum(string $column): \Ada\Core\Db\Query {
            $this->setFunction('SUM', $column);
            return $this;
        }

        public function selfJoin(string $alias): \Ada\Core\Db\Query {
            $this->addSelfJoin($alias);
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

        public function union(array $queries): \Ada\Core\Db\Query {
            $this->type          = 'union';
            $this->union_queries = $queries;
            return $this;
        }

        public function where(
            string $column,
            string $operand,
            string $value
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                $this->validateOperand($operand),
                $this->getDb()->e($value)
            );
            return $this;
        }

        public function whereBetween(
            string $column,
            string $value1,
            string $value2
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'BETWEEN',
                (
                    $this->getDb()->e($value1) .
                    ' AND ' .
                    $this->getDb()->e($value2)
                )
            );
            return $this;
        }

        public function whereColumn(
            string $column,
            string $operand,
            string $column2
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                $this->validateOperand($operand),
                $this->getDb()->q($column2)
            );
            return $this;
        }

        public function whereIn(
            string $column,
            array  $values
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'IN',
                (
                    '(' .
                        implode(', ', array_map([$this->getDb(), 'e'], $values)) .
                    ')'
                )
            );
            return $this;
        }

        public function whereNotBetween(
            string $column,
            string $value1,
            string $value2
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'NOT BETWEEN',
                (
                    $this->getDb()->e($value1) .
                    ' AND ' .
                    $this->getDb()->e($value2)
                )
            );
            return $this;
        }

        public function whereNotIn(
            string $column,
            array  $values
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'NOT IN',
                (
                    '(' .
                        implode(', ', array_map([$this->getDb(), 'e'], $values)) .
                    ')'
                )
            );
            return $this;
        }

        public function whereNotNull(string $column): \Ada\Core\Db\Query {
            $this->addWhere($column, 'IS NOT NULL', '');
            return $this;
        }

        public function whereNull(string $column): \Ada\Core\Db\Query {
            $this->addWhere($column, 'IS NULL', '');
            return $this;
        }

        protected function addColumn(
            string $name,
            string $alias = ''
        ): array {
            $this->columns[] = get_defined_vars();
            return end($this->columns);
        }

        protected function addJoin(
            string $type,
            string $table_name,
            string $alias      = '',
            bool   $add_prefix = true
        ): array {
            $this->joins[] = get_defined_vars();
            return end($this->joins);
        }

        protected function addSelfJoin(string $alias): array {
            $this->self_joins[] = get_defined_vars();
            return end($this->self_joins);
        }

        protected function addWhere(
            string $column,
            string $operand,
            string $value,
            bool   $or = false
        ): array {;
            $this->wheres[] = get_defined_vars();
            return end($this->wheres);
        }

        protected function driverExec(string $method, array $arguments) {
            return $this->getDb()->$method($this->toString(), ...$arguments);
        }

        protected function getQueryInsert(): string {

        }

        protected function getQuerySelect(): string {
            $db       = $this->getDb();
            $res      = 'SELECT ';
            $function = $this->getFunction();
            if ($function) {
                $res .= $function['name'] . '(' . $db->q($function['column']) . ')';
            }
            else {
                $res  .= $this->getDistinct() ? 'DISTINCT ' : '';
                foreach ($this->getColumns() as $column) {
                    $res .= (
                        (
                            $column['name'] == '*'
                                ? $column['name']
                                : $db->q($column['name'], $column['alias'])
                        ) .
                        ', '
                    );
                }
                $res = rtrim($res, ', ');
            }
            $from = $this->getFrom();
            $res .= ' FROM ' . (
                $db->{$from['add_prefix'] ? 't' : 'q'}(
                    $from['table_name'],
                    $from['alias']
                )
            );
            foreach ($this->getSelfJoins() as $alias) {
                $res .= ', ' . (
                    $db->{$from['add_prefix'] ? 't' : 'q'}(
                        $from['table_name'],
                        $alias
                    )
                );
            }
            foreach ($this->getJoins() as $join) {
                $res .= (
                    ' ' . (!$join['type'] ? '' : $join['type'] . ' ') . 'JOIN ' .
                    $db->{$join['add_prefix'] ? 't' : 'q'}(
                        $join['table_name'],
                        $join['alias']
                    ) .
                    (
                        isset($join['column1'])
                            ? (
                                ' ON ' .
                                $db->q($join['column1']) . ' ' .
                                $join['operand']         . ' ' .
                                $db->q($join['column2'])
                            )
                            : ''
                    )
                );
            }
            $wheres = $this->getWheres();
            if (!$wheres || !empty($wheres[0]['or'])) {
                $res .= ' WHERE TRUE';
            }
            $i = 0;
            foreach ($wheres as $where) {
                $between = in_array(
                    $where['operand'],
                    ['BETWEEN', 'NOT BETWEEN']
                );
                $res    .= (
                    ' ' .
                    ($where['or'] ? 'OR' : ($i ? 'AND' : 'WHERE')) . ' ' .
                    ($between ? '(' : '') .
                    $db->q($where['column'])                       . ' ' .
                    $where['operand']                              . ' ' .
                    $where['value'] .
                    ($between ? ')' : '')
                );
                $i++;
            }
            $group_by = $this->getGroupBy();
            if ($group_by) {
                $res .= ' GROUP BY ';
                foreach ($group_by as $column) {
                    $res .= $db->q($column) . ', ';
                }
                $res  = rtrim($res, ', ');
            }
            $order_by = $this->getOrderBy();
            if ($order_by) {
                $res .= ' ORDER BY ';
                foreach ($order_by['columns'] as $column) {
                    $res .= $db->q($column) . ', ';
                }
                $res  = rtrim($res, ', ');
                $res .= $order_by['asc'] ? ' ASC' : ' DESC';
            }
            var_dump( $this, $res );
            return preg_replace('/\s+/', ' ', $res);
        }

        protected function getQueryUnion(): string {
            return '(' .
                implode(
                    ') UNION (',
                    array_map(
                        function (\Ada\Core\Db\Query $query) {
                            return $query->toString();
                        },
                        $this->getUnionQueries()
                    )
                ) .
            ')';
        }

        protected function getQueryUpdate(): string {

        }

        protected function setFunction(string $name, string $column): array {
            $name                  = strtoupper(\Ada\Core\Clean::cmd($name));
            return $this->function = get_defined_vars();
        }

        protected function validateOperand(string $operand): string {
            $operand = strtoupper(trim(preg_replace('/\s+/', ' ', $operand)));
            if (!in_array($operand, static::OPERANDS)) {
                throw new \Ada\Core\Exception(
                    'Unknown operand \'' . $operand . '\'',
                    2
                );
            }
            return $operand;
        }

    }
