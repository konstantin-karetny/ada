<?php
    /**
    * @package   project/core
    * @version   1.0.0 04.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Column extends \Ada\Core\Db\Column {

        protected function extractParams(): array {
            $db    = $this->getDb();
            $table = $this->getTable();
            $row   = $db->fetchRow('
                SELECT ' . $db->qs([
                    'character_maximum_length',
                    'character_set_name',
                    'collation_name',
                    'column_default',
                    'column_name',
                    'data_type',
                    'is_nullable',
                    'numeric_precision',
                    'numeric_scale'
                ]) . '
                FROM '  . $db->q('information_schema.columns') . '
                WHERE ' . $db->q('table_schema') . ' LIKE ' . $db->e($table->getSchema()) . '
                AND  '  . $db->q('table_name')   . ' LIKE ' . $db->e($table->getName(true, false)) . '
                AND '   . $db->q('column_name')  . ' LIKE ' . $db->e($this->getName())
            );
            if (!$row) {
                return [];
            }
            $res = [
                'charset'       => trim($row['character_set_name']),
                'collation'     => trim($row['collation_name']),
                'default_value' => trim($row['column_default']),
                'is_nullable'   => strtolower(trim($row['is_nullable'])) == 'yes',
                'name'          => trim($row['column_name']),
                'primary_key'   => '',
                'type'          => trim($row['data_type']),
                'type_args'     => \Ada\Core\Clean::values(
                    (
                        $row['character_maximum_length']
                            ? [
                                $row['character_maximum_length']
                            ]
                            : [
                                $row['numeric_precision'],
                                $row['numeric_scale']
                            ]
                    ),
                    'int'
                ),
                'unique_key'    => ''
            ];
            $res['is_auto_increment'] = (
                in_array(
                    $res['type'],
                    [
                        'bigint',
                        'integer'
                    ]
                ) &&
                !$res['is_nullable'] &&
                stripos($res['default_value'], 'nextval') === 0
            );
            foreach ($table->getKeys() as $group => $keys) {
                foreach ($keys as $key => $names) {
                    if (in_array($this->getName(), $names)) {
                        $res[$group . '_key'] = $key;
                        break 1;
                    }
                }
            }
            return $res;
        }

    }
