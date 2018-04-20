<?php
    /**
    * @package   project/core
    * @version   1.0.0 20.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Table extends \Ada\Core\Db\Table {

        protected function extractParams(): array {
            $db  = $this->getDb();
            $row = $db->fetchRow('
                SELECT *
                FROM '  . $db->q('information_schema.tables') . '
                WHERE ' . $db->q('table_schema') . ' LIKE ' . $db->e($this->getSchema()) . '
                AND '   . $db->q('table_name')   . ' LIKE ' . $db->e($this->getName(true, false))
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
            return ('
                SELECT ' . $db->q('column_name') . '
                FROM '   . $db->q('information_schema.columns') . '
                WHERE '  . $db->q('table_schema') . ' LIKE ' . $db->e($this->getSchema()) . '
                AND '    . $db->q('table_name')   . ' LIKE ' . $db->e($this->getName(true, false))
            );
        }

    }
