<?php
    /**
    * @package   project/core
    * @version   1.0.0 21.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Table extends \Ada\Core\Db\Table {

        protected function extractKeys(): array {
            $res = [];
            $db  = $this->getDb();
            foreach ($db->fetchRows('
                SELECT ' . $db->qs([
                    'cu.column_name',
                    'tc.constraint_name',
                    'tc.constraint_type'
                ]) . '
                FROM '   . $db->q('information_schema.key_column_usage',  'cu') . '
                JOIN '   . $db->q('information_schema.table_constraints', 'tc') . '
                ON '     . $db->q('cu.table_schema')    . ' = '    . $db->q('tc.table_schema') . '
                AND '    . $db->q('cu.table_name')      . ' = '    . $db->q('tc.table_name') . '
                AND '    . $db->q('cu.constraint_name') . ' = '    . $db->q('tc.constraint_name') . '
                WHERE '  . $db->q('cu.table_schema')    . ' LIKE ' . $db->e($this->getSchema()) . '
                AND '    . $db->q('cu.table_name')      . ' LIKE ' . $db->e($this->getName(true, false))
            ) as $row) {
                $res[
                    \Ada\Core\Clean::cmd($row['constraint_type']) == 'primary key'
                        ? 'primary'
                        : 'unique'
                ][
                    trim($row['constraint_name'])
                ][] = trim($row['column_name']);
            }
            return $res;
        }

        protected function extractParams(): array {
            $db  = $this->getDb();
            $row = $db->fetchRow('
                SELECT ' . $db->q('table_schema') . '
                FROM '   . $db->q('information_schema.tables') . '
                WHERE '  . $db->q('table_schema') . ' LIKE ' . $db->e($this->getSchema()) . '
                AND '    . $db->q('table_name')   . ' LIKE ' . $db->e($this->getName(true, false))
            );
            return
                $row
                    ? [
                        'schema' => (string) $row['table_schema']
                    ]
                    : [];
        }

        protected function getQueryColumnsNames(): string {
            $db = $this->getDb();
            return '
                SELECT ' . $db->q('column_name') . '
                FROM '   . $db->q('information_schema.columns') . '
                WHERE '  . $db->q('table_schema') . ' LIKE ' . $db->e($this->getSchema()) . '
                AND '    . $db->q('table_name')   . ' LIKE ' . $db->e($this->getName(true, false));
        }

    }
