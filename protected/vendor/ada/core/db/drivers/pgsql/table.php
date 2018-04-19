<?php
    /**
    * @package   project/core
    * @version   1.0.0 19.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Table extends \Ada\Core\Db\Table {

        protected function extractProps(): array {
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

        protected static function getCreateQuery($db, array $params): string {
            $columns   = '';
            $primaries = [];
            $uniques   = [];
            $class     = $db->getNameSpace() . 'Column';
            foreach ($params['columns'] as $column_params) {
                $column_params   = $class::preapreParams($column_params);
                $columns        .= (
                    $class::getCreateQuery($db, $column_params) . ', '
                );
                if ($column_params['primary_key']) {
                    $primaries[] = $column_params['primary_key'];
                }
                if ($column_params['unique_key']) {
                    $uniques[]   = $column_params['unique_key'];
                }
            }
            $columns = rtrim($columns, ', ');
            if ($primaries) {
                $columns .= ', PRIMARY KEY (' . $db->q(reset($primaries)) . ')';
            }
            if ($uniques) {
                $columns .= ', UNIQUE (';
                foreach ($uniques as $unique) {
                    $columns .= $db->q($unique) . ', ';
                }
                $columns = rtrim($columns, ', ') . ')';
            }
            return 'CREATE TABLE ' . $db->t($params['name']) . ' (' . $columns . ')';
        }

        protected function getColumnsNamesQuery(): string {
            $db = $this->getDb();
            return ('
                SELECT ' . $db->q('column_name') . '
                FROM '   . $db->q('information_schema.columns') . '
                WHERE '  . $db->q('table_schema') . ' LIKE ' . $db->e($this->getSchema()) . '
                AND '    . $db->q('table_name')   . ' LIKE ' . $db->e($this->getName(true, false))
            );
        }

    }
