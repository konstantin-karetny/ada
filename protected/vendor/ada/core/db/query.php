<?php
    /**
    * @package   project/core
    * @version   1.0.0 15.06.2018
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
                'BETWEEN',
                'EXISTS',
                'IN',
                'IS NOT NULL',
                'IS NULL',
                'LIKE',
                'NOT BETWEEN',
                'NOT EXISTS',
                'NOT IN'
            ];

        protected
            $columns       = [],
            $db            = null,
            $distinct      = false,
            $from          = [],
            $groups_by     = [],
            $havings       = [],
            $joins         = [],
            $orders_by     = [],
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
            return $this->driverExec(__FUNCTION__);
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

        public function getGroupsBy(): array {
            return $this->groups_by;
        }

        public function getHavings(): array {
            return $this->havings;
        }

        public function getJoins(): array {
            return $this->joins;
        }

        public function getOrdersBy(): array {
            return $this->orders_by;
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
            foreach ($columns as $column) {
                $this->addGroupBy($column);
            }
            return $this;
        }

        public function having(
            string $column,
            string $operand,
            string $value
        ): \Ada\Core\Db\Query {
            $this->addHaving(
                $column,
                $operand,
                $this->getDb()->e($value)
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
                $operand,
                $this->getDb()->q($column2),
                true
            );
            return $this;
        }

        public function orderBy(array $columns): \Ada\Core\Db\Query {
            foreach ($columns as $column) {
                $this->addOrderBy(
                    ...\Ada\Core\Type::set($column, 'array', false)
                );
            }
            return $this;
        }

        public function orExists(
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                '',
                'EXISTS',
                $subquery->toString(),
                true
            );
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

        public function orNotExists(
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                '',
                'NOT EXISTS',
                $subquery->toString(),
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
                $operand,
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
                $operand,
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
                $operand,
                $this->getDb()->q($column2)
            );
            return $this;
        }

        public function whereExists(
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                '',
                'EXISTS',
                $subquery->toString()
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

        public function whereNotExists(
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                '',
                'NOT EXISTS',
                $subquery->toString()
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

        protected function addGroupBy(string $column): string {
            $this->groups_by[] = $column;
            return end($this->groups_by);
        }

        protected function addHaving(
            string $column,
            string $operand,
            string $value,
            bool   $or = false
        ): array {
            $operand         = $this->validateOperand($operand);
            $this->havings[] = get_defined_vars();
            return end($this->havings);
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

        protected function addOrderBy(
            string $column,
            bool   $asc    = true
        ): array {
            $this->orders_by[] = get_defined_vars();
            return end($this->orders_by);
        }

        protected function addWhere(
            string $column,
            string $operand,
            string $value,
            bool   $or = false
        ): array {
            $operand        = $this->validateOperand($operand);
            $this->wheres[] = get_defined_vars();
            return end($this->wheres);
        }

        protected function driverExec(string $method, array $arguments) {
            return $this->getDb()->$method($this->toString(), ...$arguments);
        }

        protected function getQueryInsert(): string {

        }

        protected function getQuerySelect(): string {
            $db  = $this->getDb();
            $res = 'SELECT ' . ($this->getDistinct() ? 'DISTINCT ' : '');
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
            $res  = rtrim($res, ', ');
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
            $i      = 0;
            $wheres = $this->getWheres();
            if (!$wheres || !empty($wheres[0]['or'])) {
                $res .= ' WHERE TRUE';
            }
            foreach ($wheres as $where) {
                $res .= ' ' . ($where['or'] ? 'OR' : ($i ? 'AND' : 'WHERE')) . ' ';
                switch ($where['operand']) {
                    case 'BETWEEN':
                    case 'NOT BETWEEN':
                        $res .= (
                            '(' .
                                $db->q($where['column']) . ' ' .
                                $where['operand']        . ' ' .
                                $where['value']          .
                            ')'
                        );
                        break;
                    case 'EXISTS':
                    case 'NOT EXISTS':
                        $res .= $where['operand'] . ' (' . $where['value'] . ')';
                        break;
                    default:
                        $res .= (
                            $db->q($where['column']) . ' ' .
                            $where['operand']        . ' ' .
                            $where['value']
                        );
                }
                $i++;
            }
            $groups_by = $this->getGroupsBy();
            if ($groups_by) {
                $res .= ' GROUP BY ';
                foreach ($groups_by as $column) {
                    $res .= $db->q($column) . ', ';
                }
                $res  = rtrim($res, ', ');
            }
            $i = 0;
            foreach ($this->getHavings() as $having) {
                $res .= (
                    ' ' .
                    ($i ? 'AND' : 'HAVING')   . ' ' .
                    $db->q($having['column']) . ' ' .
                    $having['operand']        . ' ' .
                    $having['value']
                );
                $i++;
            }
            $orders_by = $this->getOrdersBy();
            if ($orders_by) {
                $res .= ' ORDER BY ';
                foreach ($orders_by as $order_by) {
                    $res .= (
                        $db->q($order_by['column']) .
                        ($order_by['asc'] ? ' ASC' : ' DESC') .
                        ', '
                    );
                }
                $res  = rtrim($res, ', ');
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
