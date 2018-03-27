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
            var_dump( $db->fetchRows('
                SELECT *
                FROM '  . $db->q('information_schema') . '.' . $db->q('columns') . '
                WHERE ' .
                $db->q('table_name') . ' LIKE ' . $db->esc($this->getName(true))) );


exit(var_dump(


        $db->fetchRows('SELECT *
FROM   pg_index i
JOIN   pg_attribute a ON a.attrelid = i.indrelid
                     AND a.attnum = ANY(i.indkey)
WHERE  i.indrelid = \'' . $this->getName(true) . '\'::regclass
AND    i.indisprimary;')








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
