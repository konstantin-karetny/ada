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

            echo                     '<pre>
                    SELECT *
                    FROM '  . $db->q('information_schema.columns') . ' AS ' . $db->q('c') . '
                    LEFT JOIN '  . $db->q('information_schema.key_column_usage') . ' AS ' . $db->q('kcu') . '
                    ON ' . $db->q('c.table_schema') . ' = ' . $db->q('kcu.table_schema') . '
                    AND ' . $db->q('c.table_name') . ' = ' . $db->q('kcu.table_name') . '
                    AND ' . $db->q('c.column_name') . ' = ' . $db->q('kcu.column_name') . '
                    LEFT JOIN '  . $db->q('information_schema.table_constraints') . ' AS ' . $db->q('tc') . '
                    ON ' . $db->q('c.table_schema') . ' = ' . $db->q('tc.table_schema') . '
                    AND ' . $db->q('c.table_name') . ' = ' . $db->q('tc.table_name') . '
                    AND ' . $db->q('kcu.constraint_name') . ' = ' . $db->q('tc.constraint_name') . '
                    AND ' . $db->q('c.table_name') . ' LIKE ' . $db->esc($this->getName(true)) . '
                    AND ' . $db->q('kcu.constraint_name') . ' IS NOT NULL
                    AND ' . $db->q('tc.constraint_name') . ' IS NOT NULL
                ';die;

            exit(var_dump(



                $db->fetchRows('
                    SELECT *
                    FROM '  . $db->q('information_schema.columns') . ' AS ' . $db->q('c') . '
                    LEFT JOIN '  . $db->q('information_schema.key_column_usage') . ' AS ' . $db->q('kcu') . '
                    ON ' . $db->q('c.table_schema') . ' = ' . $db->q('kcu.table_schema') . '
                    AND ' . $db->q('c.table_name') . ' = ' . $db->q('kcu.table_name') . '
                    AND ' . $db->q('c.column_name') . ' = ' . $db->q('kcu.column_name') . '
                    LEFT JOIN '  . $db->q('information_schema.table_constraints') . ' AS ' . $db->q('tc') . '
                    ON ' . $db->q('c.table_schema') . ' = ' . $db->q('tc.table_schema') . '
                    AND ' . $db->q('c.table_name') . ' = ' . $db->q('tc.table_name') . '
                    AND ' . $db->q('kcu.constraint_name') . ' = ' . $db->q('tc.constraint_name') . '
                    AND ' . $db->q('c.table_name') . ' LIKE ' . $db->esc($this->getName(true)) . '
                    AND ' . $db->q('kcu.constraint_name') . ' IS NOT NULL
                    AND ' . $db->q('tc.constraint_name') . ' IS NOT NULL
                ')

            ));

'select *
from
    information_schema.table_constraints tc,
    information_schema.key_column_usage kc
where
    c.table_name LIKE ' . $db->esc($this->getName(true)) . '
    AND c.column_name = kc.column_name
    AND kc.table_name = tc.table_name
    and kc.table_schema = tc.table_schema
    and kc.constraint_name = tc.constraint_name';


            exit(var_dump(
                $db->fetchRows('
                    SELECT *
                    FROM '  . $db->q('information_schema') . '.' . $db->q('columns') . '
                    WHERE ' .
                    $db->q('table_name') . ' LIKE ' . $db->esc($this->getName(true))
                )
            ));


            foreach ($db->fetchRows('
                SELECT *
                FROM '  . $db->q('information_schema') . '.' . $db->q('columns') . '
                WHERE ' .
                $db->q('table_name') . ' LIKE ' . $db->esc($this->getName(true))
            ) as $params) {
                $column = Column::init($params['column_name'], $this);
                $column->setCollation((string) $params['collation_name']);
                $column->setDefaultValue($params['column_default']);
                $column->setLength((int) $params['character_maximum_length']);
                $column->setIsNull($params['is_nullable'] == 'YES');
                //$column->setIsPrimaryKey($params['Key'] == 'PRI');
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
