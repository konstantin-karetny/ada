<?php
    /**
    * @package   project/core
    * @version   1.0.0 13.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\MySQL;

    class Table extends \Ada\Core\Db\Table {

        protected
            $engine = 'InnoDB';

        protected function getColumnsNamesQuery(): string {
            $db = $this->getDb();
            return ('
                SELECT ' . $db->q('COLUMN_NAME') . '
                FROM '   . $db->q('INFORMATION_SCHEMA.COLUMNS') . '
                WHERE '  . $db->q('TABLE_SCHEMA') . ' LIKE ' . $db->e($db->getName()) . '
                AND '    . $db->q('TABLE_NAME')   . ' LIKE ' . $db->e($this->getName(true))
            );
        }

        protected function getProps(): array {
            $db  = $this->getDb();
            $row = $db->fetchRow('
                SELECT *
                FROM '  . $db->q('information_schema.TABLES', 't') . '
                JOIN '  . $db->q('information_schema.COLLATION_CHARACTER_SET_APPLICABILITY', 'ccsa') . '
                ON '    . $db->q('ccsa.COLLATION_NAME') . ' = '    . $db->q('t.TABLE_COLLATION') . '
                WHERE ' . $db->q('t.TABLE_SCHEMA')      . ' LIKE ' . $db->e($db->getName()) . '
                AND '   . $db->q('t.TABLE_NAME')        . ' LIKE ' . $db->e($this->getName(true)) . '
            ');
            return
                $row
                    ? [
                        'charset'   => trim($row['CHARACTER_SET_NAME']),
                        'collation' => trim($row['COLLATION_NAME']),
                        'engine'    => trim($row['ENGINE']),
                        'exists'    => true,
                        'schema'    => trim($row['TABLE_SCHEMA'])
                    ]
                    : [];
        }

    }
