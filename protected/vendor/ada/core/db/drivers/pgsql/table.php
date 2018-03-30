<?php
    /**
    * @package   project/core
    * @version   1.0.0 30.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Table extends \Ada\Core\Db\Table {

        public function create(): bool {
            $db          = $this->getDb();
            $query       = 'CREATE TABLE ' . $db->t($this->getName()) . ' (';
            $constraints = '';
            foreach ($this->getColumns() as $column) {
                $query  .= '
                    ' . (
                    $db->q($column->getName()) . ' ' .
                    (
                        $column->getIsAutoIncrement()
                            ? 'serial'
                            : (
                                $column->getType() .
                                (
                                    !$column->getLength()
                                        ? ''
                                        : '(' . $db->e($column->getLength()) . ')'
                                ) .
                                (
                                    $column->getDefaultValue() === ''
                                        ? ''
                                        : ' DEFAULT ' . $column->getDefaultValue()
                                ) .
                                (
                                    !$column->getCollation()
                                        ? ''
                                        : ' COLLATE ' . $db->e($column->getCollation())
                                )
                            )
                    ) .
                    (
                        ($column->getIsNull() ? '' : ' NOT') . ' NULL'
                    )
                ) . ',';
                if ($column->getIsPrimaryKey()) {
                    $constraints .= ('
                        PRIMARY KEY (' . $db->q($column->getName()) . '),
                    ');
                }
                if ($column->getIsUniqueKey()) {
                    $constraints .= ('
                        UNIQUE ('      . $db->q($column->getName()) . '),
                    ');
                }
            }
            $query = (
                rtrim($query, " \t\n\r\0\x0B,") .
                (
                    !$constraints
                        ? ''
                        : ',' . rtrim($constraints, " \t\n\r\0\x0B,")
                ) . '
                )'
            );
            if (!$db->exec($query)) {
                return false;
            }
            $this->reInit();
            return true;
        }

        protected function fetchColumns(bool $cached = true): array {
            if ($cached && isset($this->cache['columns'])) {
                return $this->cache['columns'];
            }
            if (!$this->exists()) {
                return [];
            }
            $db                     = $this->getDb();
            $this->cache['columns'] = [];
            foreach ($db->fetchRows('
                SELECT *
                FROM '       . $db->q('information_schema.key_column_usage', 'kcu') . '
                RIGHT JOIN ' . $db->q('information_schema.table_constraints', 'tc') . '
                ON '         . $db->q('kcu.table_schema')    . ' = '    . $db->q('tc.table_schema') . '
                AND '        . $db->q('kcu.table_name')      . ' = '    . $db->q('tc.table_name') . '
                AND '        . $db->q('kcu.constraint_name') . ' = '    . $db->q('tc.constraint_name') . '
                RIGHT JOIN ' . $db->q('information_schema.columns', 'c') . '
                ON '         . $db->q('c.table_schema')      . ' = '    . $db->q('kcu.table_schema') . '
                AND '        . $db->q('c.table_name')        . ' = '    . $db->q('kcu.table_name') . '
                AND '        . $db->q('c.column_name')       . ' = '    . $db->q('kcu.column_name') . '
                WHERE '      . $db->q('c.table_name')        . ' LIKE ' . $db->e($this->getName(true)) . '
            ') as $row) {
                $column = Column::init($row['column_name'], $this);
                $column->setCollation((string) $row['collation_name']);
                $column->setDefaultValue($row['column_default']);
                $column->setLength((int) $row['character_maximum_length']);
                $column->setIsNull(
                    strtoupper(trim($row['is_nullable'])) == 'YES'
                );
                $column->setIsPrimaryKey(
                    strtoupper(trim($row['constraint_type'])) == 'PRIMARY KEY'
                );
                $column->setIsUniqueKey(
                    strtoupper(trim($row['constraint_type'])) == 'UNIQUE'
                );
                $column->setType($row['data_type']);
                $column->setIsAutoIncrement(
                    in_array(
                        $column->getType(),
                        [
                            'bigint',
                            'integer'
                        ]
                    ) &&
                    !$column->getIsNull() &&
                    stripos($column->getDefaultValue(), 'nextval') === 0
                );
                $this->cache['columns'][$column->getName()] = $column;
            }
            return $this->cache['columns'];
        }

        protected function fetchDbData(bool $cached = true): array {
            $keys = [
                'schema',
                'exists'
            ];
            if ($cached && \Ada\Core\Arr::keysExist($this->cache, $keys)) {
                return array_intersect_key($this->cache, array_flip($keys));
            }
            $db  = $this->getDb();
            $row = $db->fetchRow('
                SELECT *
                FROM '  . $db->q('information_schema') . '.' . $db->q('tables') . '
                WHERE ' . $db->q('table_name') . ' LIKE ' . $db->e($this->getName(true))
            );
            if (!$row) {
                return [];
            }
            $res = [
                'schema' => (string) $row['table_schema'],
                'exists' => true
            ];
            $this->cache = array_merge($this->cache, $res);
            return $res;
        }

    }
