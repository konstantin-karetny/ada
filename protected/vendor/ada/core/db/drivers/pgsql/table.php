<?php
    /**
    * @package   project/core
    * @version   1.0.0 21.06.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Table extends \Ada\Core\Db\Table {

        protected function extractKeys(): array {
            $res = [];
            foreach ((
                $this->getDb()->getQuery()
                    ->select([
                        'cu.column_name',
                        'tc.constraint_name',
                        'tc.constraint_type'
                    ])
                    ->from('information_schema.key_column_usage',  'cu', false)
                    ->join('information_schema.table_constraints', 'tc', false)
                    ->on('cu.table_schema',             '=', 'tc.table_schema')
                    ->whereColumn('cu.table_name',      '=', 'tc.table_name')
                    ->whereColumn('cu.constraint_name', '=', 'tc.constraint_name')
                    ->where('cu.table_schema',          '=', $this->getSchema())
                    ->where('cu.table_name',            '=', $this->getName(true, false))
                    ->fetchRows()
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
            $db     = $this->getDb();
            $schema = (string) $db->getQuery()
                ->selectOne('table_schema')
                ->from('information_schema.tables', '', false)
                ->where('table_schema', '=', $db->getSchema())
                ->where('table_name',   '=', $this->getName(true, false))
                ->fetchCell();
            return $schema ? ['schema' => $schema] : [];
        }

        protected function getColumnsNames(): array {
            $db = $this->getDb();
            return $db->getQuery()
                ->selectOne('column_name')
                ->from('information_schema.columns', '', false)
                ->where('table_schema', '=', $db->getSchema())
                ->where('table_name',   '=', $this->getName(true, false))
                ->fetchColumn();
        }

    }
