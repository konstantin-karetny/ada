<?php
    /**
    * @package   project/core
    * @version   1.0.0 19.06.2018
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
            $groups_by     = [],
            $havings       = [],
            $joins         = [],
            $orders_by     = [],
            $self_joins    = [],
            $table         = [],
            $type          = 'select',
            $union_queries = [],
            $values        = [],
            $wheres        = [];

        public static function init(\Ada\Core\Db\Driver $db): \Ada\Core\Db\Query {
            return new static(...func_get_args());
        }

        protected function __construct(\Ada\Core\Db\Driver $db) {
            $this->db = $db;
        }

        public function delete(): \Ada\Core\Db\Query {
            $this->type = 'delete';
            return $this;
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

        public function getColumns(): array {
            return $this->columns;
        }

        public function getDb(): \Ada\Core\Db\Driver {
            return $this->db;
        }

        public function getDistinct(): bool {
            return $this->distinct;
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

        public function getTable(): array {
            return $this->table;
        }

        public function getType(): string {
            return $this->type;
        }

        public function getUnionQueries(): array {
            return $this->union_queries;
        }

        public function getValues(): array {
            return $this->values;
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
                $this->validateOperand($operand),
                $this->getDb()->e($value)
            );
            return $this;
        }

        public function insert(array $values): \Ada\Core\Db\Query {
            if (!$values) {
                throw new \Ada\Core\Exception(
                    '\'values\' argument can not be empty',
                    2
                );
            }
            $this->type   = 'insert';
            $this->values = $values;
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

        public function orAll(
            string             $column,
            string             $operand,
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                $this->validateOperand($operand) . ' ALL',
                '(' . $subquery->toString() . ')',
                true
            );
            return $this;
        }

        public function orAny(
            string             $column,
            string             $operand,
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                $this->validateOperand($operand) . ' ANY',
                '(' . $subquery->toString() . ')',
                true
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
                '(' . $subquery->toString() . ')',
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
                '(' . $subquery->toString() . ')',
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

        public function select(array $columns = []): \Ada\Core\Db\Query {
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

        public function table(
            string $name,
            string $alias      = '',
            bool   $add_prefix = true
        ): \Ada\Core\Db\Query {
            $this->table = get_defined_vars();
            return $this;
        }

        public function toString(): string {
            if ($this->getType() != 'union' && !$this->getTable()) {
                throw new \Ada\Core\Exception('No table specified', 1);
            }
            $res = preg_replace(
                '/\s+/',
                ' ',
                $this->{'getQuery' . ucfirst($this->getType())}()
            );
            return
                substr($res, -1, 1) === ' ' &&
                substr($res, -2, 1) !== ':'
                    ? trim($res)
                    : $res;
        }

        public function union(array $queries): \Ada\Core\Db\Query {
            $this->type          = 'union';
            $this->union_queries = $queries;
            return $this;
        }

        public function update(array $values): \Ada\Core\Db\Query {
            if (!$values) {
                throw new \Ada\Core\Exception(
                    '\'values\' argument can not be empty',
                    2
                );
            }
            $this->type   = 'update';
            $this->values = $values;
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

        public function whereAll(
            string             $column,
            string             $operand,
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                $this->validateOperand($operand) . ' ALL',
                '( ' . $subquery->toString() . ' )'
            );
            return $this;
        }

        public function whereAny(
            string             $column,
            string             $operand,
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                $this->validateOperand($operand) . ' ANY',
                '( ' . $subquery->toString() . ' )'
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

        public function whereExists(
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                '',
                'EXISTS',
                '(' . $subquery->toString() . ')'
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
                '(' . $subquery->toString() . ')'
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
            string $value
        ): array {
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
            $this->wheres[] = get_defined_vars();
            return end($this->wheres);
        }

        protected function driverExec(string $method, array $arguments = []) {
            return $this->getDb()->$method($this->toString(), ...$arguments);
        }

        protected function getPartColumns(array $columns = []): string {
            $columns = $columns ? $columns : $this->getColumns();
            if (!$columns) {
                return '*';
            }
            $db = $this->getDb();
            return implode(
                ', ',
                array_map(
                    function($el) use (&$db) {
                        return $db->q($el['name'], $el['alias']);
                    },
                    $columns
                )
            );
        }

        protected function getPartGroupsBy(array $groups_by = []): string {
            $groups_by = $groups_by ? $groups_by : $this->getGroupsBy();
            if (!$groups_by) {
                return '';
            }
            $res = 'GROUP BY ';
            $db  = $this->getDb();
            foreach ($groups_by as $column) {
                $res .= $db->q($column) . ', ';
            }
            return rtrim($res, ', ');
        }

        protected function getPartHavings(array $havings = []): string {
            $res = '';
            $i   = 0;
            $db  = $this->getDb();
            foreach ($havings ? $havings : $this->getHavings() as $having) {
                $res .= (
                    ' ' .
                    ($i ? 'AND' : 'HAVING')   . ' ' .
                    $db->q($having['column']) . ' ' .
                    $having['operand']        . ' ' .
                    $having['value']
                );
                $i++;
            }
            return $res;
        }

        protected function getPartJoins(array $joins = []): string {
            $res = '';
            foreach ($joins ? $joins : $this->getJoins() as $join) {
                $res .= (
                    ' ' . (!$join['type'] ? '' : $join['type'] . ' ') . 'JOIN ' .
                    $this->getPartTable($join) .
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
            return $res;
        }

        protected function getPartOrdersBy(array $orders_by = []): string {
            $orders_by = $orders_by ? $orders_by : $this->getOrdersBy();
            if (!$orders_by) {
                return '';
            }
            $res .= 'ORDER BY ';
            $db   = $this->getDb();
            foreach ($orders_by as $order_by) {
                $res .= (
                    $db->q($order_by['column']) .
                    ($order_by['asc'] ? ' ASC' : ' DESC') .
                    ', '
                );
            }
            return rtrim($res, ', ');
        }

        protected function getPartSelfJoins(
            array $table   = [],
            array $aliases = []
        ): string {
            $res   = '';
            $table = $table ? $table : $this->getTable();
            foreach ($aliases ? $aliases : $this->getSelfJoins() as $alias) {
                $res .= ', ' . $this->getPartTable(
                    array_merge($table, ['alias' => $alias])
                );
            }
            return $res;
        }

        protected function getPartTable(array $table = []): string {
            $table = $table ? $table : $this->getTable();
            return $this->getDb()->{$table['add_prefix'] ? 't' : 'q'}(
                $table['name'],
                $table['alias']
            );
        }

        protected function getPartWhere(array $wheres = []): string {
            $res    = '';
            $i      = 0;
            $db     = $this->getDb();
            $wheres = $wheres ? $wheres : $this->getWheres();
            if (!$wheres || !empty($wheres[0]['or'])) {
                $res .= 'WHERE TRUE';
            }
            foreach ($wheres as $where) {
                $res .= (
                    ' ' . ($where['or'] ? 'OR' : ($i ? 'AND' : 'WHERE')) . ' ' .
                    $db->q($where['column']) . ' ' .
                    $where['operand'] . ' ' .
                    $where['value']
                );
                $i++;
            }
            return $res;
        }

        protected function getQueryDelete(): string {
            return
                'DELETE FROM ' . $this->getPartTable() .
                ' '            . $this->getPartWhere();
        }

        protected function getQueryInsert(): string {
            $db     = $this->getDb();
            $values = $this->getValues();
            return
                'INSERT INTO '  . $this->getPartTable() . ' (' .
                    implode(', ', array_map([$db, 'q'], array_keys($values))) .
                ') VALUES (' .
                    implode(', ', array_map([$db, 'e'], $values)) .
                ')';
        }


        protected function getQuerySelect(): string {
            return
                'SELECT ' .
               ($this->getDistinct() ? 'DISTINCT ' : '') .
                $this->getPartColumns()   . ' ' .
                'FROM ' .
                $this->getPartTable()     . ' ' .
                $this->getPartSelfJoins() . ' ' .
                $this->getPartJoins()     . ' ' .
                $this->getPartWhere()     . ' ' .
                $this->getPartGroupsBy()  . ' ' .
                $this->getPartHavings()   . ' ' .
                $this->getPartOrdersBy();
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
            $res = 'UPDATE ' . $this->getPartTable() . ' SET ';
            $db  = $this->getDb();
            foreach ($this->getValues() as $k => $v) {
                $res .= $db->q($k) . ' = ' . $db->e($v) . ', ';
            }
            return rtrim($res, ', ') . ' ' . $this->getPartWhere();
        }

        protected function validateOperand(string $operand): string {
            $res = strtoupper(trim(preg_replace('/\s+/', ' ', $operand)));
            if (!in_array($res, static::OPERANDS)) {
                throw new \Ada\Core\Exception(
                    'Unknown operand \'' . $operand . '\'',
                    3
                );
            }
            return $res;
        }

    }
