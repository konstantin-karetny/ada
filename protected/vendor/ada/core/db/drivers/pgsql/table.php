<?php
    /**
    * @package   project/core
    * @version   1.0.0 21.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Table extends \Ada\Core\Db\Table {

        protected function load(bool $cached): bool {
            if ($cached && $this->cache(__FUNCTION__)) {
                return $this->cache(__FUNCTION__);
            }
            $db   = $this->getDb();
            $load = $db->fetchRow('
                SELECT *
                FROM '  . $db->q('information_schema') . '.' . $db->q('tables') . '
                WHERE ' .
                $db->q('table_name') . ' LIKE ' . $db->esc($this->getName(true))
            );
            if (!$load) {
                return false;
            }
            $this->cache(__FUNCTION__, $load);
            return true;
        }

        protected function loadColumns(): array {
            $res = [];
            if (!$this->exists()) {
                return $res;
            }
            $db = $this->getDb();
            foreach ($db->fetchRows('
                SELECT *
                FROM '      . $db->q('information_schema.columns', 'c') . '
                LEFT JOIN ' . $db->q('information_schema.key_column_usage', 'kcu') . '
                ON '        . $db->q('c.table_schema')      . ' = '    . $db->q('kcu.table_schema') . '
                AND '       . $db->q('c.table_name')        . ' = '    . $db->q('kcu.table_name') . '
                AND '       . $db->q('c.column_name')       . ' = '    . $db->q('kcu.column_name') . '
                LEFT JOIN ' . $db->q('information_schema.table_constraints', 'tc') . '
                ON '        . $db->q('c.table_schema')      . ' = '    . $db->q('tc.table_schema') . '
                AND '       . $db->q('c.table_name')        . ' = '    . $db->q('tc.table_name') . '
                AND '       . $db->q('kcu.constraint_name') . ' = '    . $db->q('tc.constraint_name') . '
                WHERE '     . $db->q('c.table_name')        . ' LIKE ' . $db->esc($this->getName(true)) . '
            ') as $params) {
                exit(var_dump( $params ));
                $column = Column::init($params['column_name'], $this);
                $column->setCollation((string) $params['collation_name']);
                $column->setDefaultValue($params['column_default']);
                $column->setLength((int) $params['character_maximum_length']);
                $column->setIsNull(
                    strtoupper(trim($params['is_nullable'])) == 'YES'
                );
                $column->setIsPrimaryKey(
                    strtoupper(trim($params['constraint_type'])) == 'PRIMARY KEY'
                );
                $column->setType($params['data_type']);
                $column->setIsAutoIncrement(
                    in_array(
                        $column->getType(),
                        [
                            'bigint',
                            'integer'
                        ]
                    ) &&
                    !$column->isNull() &&
                    stripos($column->getDefaultValue(), 'nextval') === 0
                );
                exit(var_dump( $column ));
                $res[$column->getName()] = $column;
            }
            return $res;
        }

    }
