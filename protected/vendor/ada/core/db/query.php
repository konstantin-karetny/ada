<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Query extends \Ada\Core\Proto {

        const
            OPERANDS   = [
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
            $columns   = [],
            $db        = null,
            $distinct  = false,
            $groups_by = [],
            $havings   = [],
            $joins     = [],
            $orders_by = [],
            $row       = [],
            $table     = [],
            $type      = 'select',
            $union     = [],
            $wheres    = [];

        public static function init(\Ada\Core\Db\Driver $db): \Ada\Core\Db\Query {
            return new static($db);
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

        public function dropColumns(): \Ada\Core\Db\Query {
            $this->columns = [];
            return $this;
        }

        public function dropGroupsBy(): \Ada\Core\Db\Query {
            $this->groups_by = [];
            return $this;
        }

        public function dropHavings(): \Ada\Core\Db\Query {
            $this->havings = [];
            return $this;
        }

        public function dropJoins(): \Ada\Core\Db\Query {
            $this->joins = [];
            return $this;
        }

        public function dropOrdersBy(): \Ada\Core\Db\Query {
            $this->orders_by = [];
            return $this;
        }

        public function dropWheres(): \Ada\Core\Db\Query {
            $this->wheres = [];
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
            $this->addTable(...func_get_args());
            return $this;
        }

        public function fromSub(
            \Ada\Core\Db\Query $subquery,
            string             $alias
        ): \Ada\Core\Db\Query {
            $this->addTable('', $alias, true, $subquery);
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

        public function getRow(): array {
            return $this->row;
        }

        public function getTable(): array {
            return $this->table;
        }

        public function getType(): string {
            return $this->type;
        }

        public function getUnion(): array {
            return $this->union;
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
            if ($value === '') {
                throw new \Ada\Core\Exception(
                    'Argument 3 passed to ' . __METHOD__ . '() must not be empty',
                    1
                );
            }
            $this->addHaving(
                $column,
                $this->validateOperand($operand),
                $this->getDb()->e($value)
            );
            return $this;
        }

        public function insert(array $row): \Ada\Core\Db\Query {
            if (!$row) {
                throw new \Ada\Core\Exception(
                    'Argument 1 passed to ' . __METHOD__ . '() must not be empty',
                    2
                );
            }
            $this->type = 'insert';
            $this->row  = $row;
            return $this;
        }

        public function into(
            string $table_name,
            string $alias      = '',
            bool   $add_prefix = true
        ): \Ada\Core\Db\Query {
            $this->addTable(...func_get_args());
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
            return $this->onMulti(
                $this->getDb()->getQuery()
                    ->whereColumn(
                        $column1,
                        $this->validateOperand($operand),
                        $column2
                    )
            );
        }

        public function onMulti(
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            if (!$this->joins) {
                return $this;
            }
            $this->joins[
                \Ada\Core\Type\Arr::init($this->joins)->lastKey()
            ]['ons'][] = $subquery->getWheres();
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
                '(' . $subquery->toStr() . ')',
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
                '(' . $subquery->toStr() . ')',
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
                    ...\Ada\Core\Types::set($column, 'arr', false)
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
                '(' . $subquery->toStr() . ')',
                true
            );
            return $this;
        }

        public function orMulti(
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                '',
                '',
                '(' . $this->getPartWhere($subquery->getWheres(), false) . ')',
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

        public function orInSub(
            string             $column,
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'IN',
                '(' . $subquery->toStr() . ')',
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
                '(' . $subquery->toStr() . ')',
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

        public function orNotInSub(
            string             $column,
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'NOT IN',
                '(' . $subquery->toStr() . ')',
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

        public function orRaw(string $condition): \Ada\Core\Db\Query {
            $this->addWhere('', '', $condition, true);
            return $this;
        }

        public function orSub(
            string             $column,
            string             $operand,
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                $this->validateOperand($operand),
                '(' . $subquery->toStr() . ')',
                true
            );
            return $this;
        }

        public function orWhere(
            string $column,
            string $operand,
            string $value
        ): \Ada\Core\Db\Query {
            if ($value === '') {
                throw new \Ada\Core\Exception(
                    'Argument 3 passed to ' . __METHOD__ . '() must not be empty',
                    3
                );
            }
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
            foreach ($columns as $column) {
                $args = \Ada\Core\Types::set($column, 'arr', false);
                is_string(reset($args))
                    ? $this->selectOne(...$args)
                    : $this->selectSub(...$args);
            }
            return $this;
        }

        public function selectOne(
            string $name,
            string $alias = ''
        ): \Ada\Core\Db\Query {
            $this->type = 'select';
            $this->addColumn(...func_get_args());
            return $this;
        }

        public function selectSub(
            \Ada\Core\Db\Query $subquery,
            string             $alias = ''
        ): \Ada\Core\Db\Query {
            $this->type = 'select';
            $this->addColumn('', $alias, $subquery);
            return $this;
        }

        public function table(
            string $table_name,
            string $alias      = '',
            bool   $add_prefix = true
        ): \Ada\Core\Db\Query {
            $this->addTable(...func_get_args());
            return $this;
        }

        public function toStr(): string {
            if ($this->getType() != 'union' && !$this->getTable()) {
                throw new \Ada\Core\Exception('No table specified', 4);
            }
            return ltrim(
                \Ada\Core\Type\Str::init(
                    $this->{'getQuery' . ucfirst($this->getType())}()
                )->oneLine(false)
            );
        }

        public function union(array $queries): \Ada\Core\Db\Query {
            $this->addUnion($queries);
            return $this;
        }

        public function unionAll(array $queries): \Ada\Core\Db\Query {
            $this->addUnion($queries, true);
            return $this;
        }

        public function update(array $row): \Ada\Core\Db\Query {
            if (!$row) {
                throw new \Ada\Core\Exception(
                    'Argument 1 passed to ' . __METHOD__ . '() must not be empty',
                    5
                );
            }
            $this->type = 'update';
            $this->row  = $row;
            return $this;
        }

        public function where(
            string $column,
            string $operand,
            string $value
        ): \Ada\Core\Db\Query {
            if ($value === '') {
                throw new \Ada\Core\Exception(
                    'Argument 3 passed to ' . __METHOD__ . '() must not be empty',
                    6
                );
            }
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
                '( ' . $subquery->toStr() . ' )'
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
                '( ' . $subquery->toStr() . ' )'
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
                '(' . $subquery->toStr() . ')'
            );
            return $this;
        }

        public function whereMulti(
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                '',
                '',
                '(' . $this->getPartWhere($subquery->getWheres(), false) . ')'
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

        public function whereInSub(
            string             $column,
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'IN',
                '(' . $subquery->toStr() . ')'
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
                '(' . $subquery->toStr() . ')'
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

        public function whereNotInSub(
            string             $column,
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                'NOT IN',
                '(' . $subquery->toStr() . ')'
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

        public function whereRaw(string $condition): \Ada\Core\Db\Query {
            $this->addWhere('', '', $condition);
            return $this;
        }

        public function whereSub(
            string             $column,
            string             $operand,
            \Ada\Core\Db\Query $subquery
        ): \Ada\Core\Db\Query {
            $this->addWhere(
                $column,
                $this->validateOperand($operand),
                '(' . $subquery->toStr() . ')'
            );
            return $this;
        }

        protected function addColumn(
            string             $name,
            string             $alias    = '',
            \Ada\Core\Db\Query $subquery = null
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
            $ons           = [];
            $subquery      = null;
            $this->joins[] = get_defined_vars();
            return end($this->joins);
        }

        protected function addOrderBy(
            string $column,
            bool   $asc = true
        ): array {
            $this->orders_by[] = get_defined_vars();
            return end($this->orders_by);
        }

        protected function addTable(
            string             $table_name,
            string             $alias      = '',
            bool               $add_prefix = true,
            \Ada\Core\Db\Query $subquery   = null
        ): array {
            $this->table = get_defined_vars();
            return $this->getTable();
        }

        protected function addUnion(
            array $queries,
            bool  $all = false
        ): array {
            $this->type  = 'union';
            $this->union = get_defined_vars();
            return $this->getUnion();
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
            return $this->getDb()->$method($this->toStr(), ...$arguments);
        }

        protected function getPartColumns(array $columns = []): string {
            $columns = $columns ? $columns : $this->getColumns();
            if (!$columns) {
                return '*';
            }
            $res = '';
            $db  = $this->getDb();
            foreach ($columns as $column) {
                $res .= (
                    $column['subquery'] === null
                        ? $db->q($column['name'], $column['alias'])
                        : (
                            '('. $column['subquery']->toStr() . ')' .
                            (
                                $column['alias'] === ''
                                    ? ''
                                    : ' AS ' . $db->q($column['alias'])
                            )
                        )
                ) . ', ';
            }
            return rtrim($res, ', ');
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
            $db  = $this->getDb();
            $ons = function (array $ons) {
                $res = '';
                $i   = 0;
                foreach ($ons as $on) {
                    $res .= (
                        ($i ? ' AND ' : ' ON ') .
                        $this->getPartWhere($on, false)
                    );
                    $i++;
                }
                return $res;
            };
            foreach ($joins ? $joins : $this->getJoins() as $join) {
                $res .= (
                    ' ' . (!$join['type'] ? '' : $join['type'] . ' ') . 'JOIN ' .
                    $this->getPartTable($join) .
                    $ons($join['ons'])
                );
            }
            return $res;
        }

        protected function getPartOrdersBy(array $orders_by = []): string {
            $orders_by = $orders_by ? $orders_by : $this->getOrdersBy();
            if (!$orders_by) {
                return '';
            }
            $res = 'ORDER BY ';
            $db  = $this->getDb();
            foreach ($orders_by as $order_by) {
                $res .= (
                    $db->q($order_by['column']) .
                    ($order_by['asc'] ? ' ASC' : ' DESC') .
                    ', '
                );
            }
            return rtrim($res, ', ');
        }

        protected function getPartTable(array $table = []): string {
            $db    = $this->getDb();
            $table = $table ? $table : $this->getTable();
            if ($table['subquery'] === null) {
                return $db->{$table['add_prefix'] ? 't' : 'q'}(
                    $table['table_name'],
                    $table['alias']
                );
            }
            return
                '(' . $table['subquery']->toStr() . ')' .
                (
                    $table['alias'] === ''
                        ? ''
                        : ' AS ' . $db->q($table['alias'])
                );
        }

        protected function getPartWhere(
            array $wheres = [],
            bool  $intro  = true
        ): string {
            $res    = '';
            $i      = 0;
            $db     = $this->getDb();
            $wheres = $wheres ? $wheres : $this->getWheres();
            if (!$wheres || !empty($wheres[0]['or'])) {
                $res .= $intro ? 'WHERE TRUE' : '';
            }
            foreach ($wheres as $where) {
                $res .= (
                    (
                        !$i && !$intro
                            ? ''
                            : ' ' . ($where['or'] ? 'OR' : ($i ? 'AND' : 'WHERE')) . ' '
                    ) .
                    (!$where['column'] ? '' : $db->q($where['column'])  . ' ') .
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
            $db  = $this->getDb();
            $row = $this->getRow();
            return
                'INSERT INTO '  . $this->getPartTable() . ' (' .
                    implode(', ', array_map([$db, 'q'], array_keys($row))) .
                ') VALUES (' .
                    implode(', ', array_map([$db, 'e'], array_values($row))) .
                ')';
        }


        protected function getQuerySelect(): string {
            return
                'SELECT ' .
               ($this->getDistinct() ? 'DISTINCT ' : '') .
                $this->getPartColumns()  . ' ' .
                'FROM ' .
                $this->getPartTable()    . ' ' .
                $this->getPartJoins()    . ' ' .
                $this->getPartWhere()    . ' ' .
                $this->getPartGroupsBy() . ' ' .
                $this->getPartHavings()  . ' ' .
                $this->getPartOrdersBy();
        }

        protected function getQueryUnion(): string {
            $union = $this->getUnion();
            return
                implode(
                    ' UNION ' . ($union['all'] ? 'ALL ' : ''),
                    array_map(
                        function (\Ada\Core\Db\Query $query) {
                            return $query->toStr();
                        },
                        $union['queries']
                    )
                );
        }

        protected function getQueryUpdate(): string {
            $res = 'UPDATE ' . $this->getPartTable() . ' SET ';
            $db  = $this->getDb();
            foreach ($this->getRow() as $k => $v) {
                $res .= $db->q($k) . ' = ' . $db->e($v) . ', ';
            }
            return rtrim($res, ', ') . ' ' . $this->getPartWhere();
        }

        protected function validateOperand(string $operand): string {
            $res = strtoupper(\Ada\Core\Type\Str::init($operand)->oneLine());
            if (!in_array($res, static::OPERANDS)) {
                throw new \Ada\Core\Exception(
                    'Unknown operand \'' . $operand . '\'',
                    7
                );
            }
            return $res;
        }

    }
