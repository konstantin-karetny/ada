<?php
    /**
    * @package   project/core
    * @version   1.0.0 17.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Column extends \Ada\Core\Db\Column {

        public static function getCreateQuery($db, array  $params): string {
            return (
                $db->q($params['name'])   . ' ' .
                (
                    $params['is_auto_increment']
                        ? 'serial'
                        : (
                            $params['type'] .
                            (
                                !$params['length']
                                    ? ''
                                    : '(' . $db->e($params['length']) . ')'
                            ) .
                            (
                                $params['default_value'] === ''
                                    ? ''
                                    : ' DEFAULT \'' . $params['default_value'] . '\''
                            ) .
                            (
                                !$params['collation']
                                    ? ''
                                    : ' COLLATE ' . $db->e($params['collation'])
                            )
                        )
                ) .
                (
                    ($params['is_nullable']? '' : ' NOT') . ' NULL'
                )
            );
        }

        protected function getProps(): array {
            $table = $this->getTable();
            $db    = $table->getDb();
            $row   = $db->fetchRow('
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
                WHERE '      . $db->q('c.table_name')        . ' LIKE ' . $db->e($table->getName(true, false)) . '
                AND '        . $db->q('c.column_name')       . ' LIKE ' . $db->e($this->getName())
            );
            if (!$row) {
                return [];
            }
            $constraint_type = strtolower(trim($row['constraint_type']));
            $res             = [
                'charset'        => trim($row['character_set_name']),
                'collation'      => trim($row['collation_name']),
                'default_value'  => trim($row['column_default']),
                'is_nullable'    => strtolower(trim($row['is_nullable'])) == 'yes',
                'is_primary_key' => $constraint_type == 'primary key',
                'is_unique_key'  => $constraint_type == 'unique',
                'length'         => (int) $row['character_maximum_length'],
                'type'           => trim($row['data_type'])
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
            return $res;
        }

        protected function getRenameQuery(string $name): string {
            $db = $this->getDb();
            return '
                ALTER TABLE '   . $db->t($this->getTable()->getName()) . '
                RENAME COLUMN ' . $db->q($this->getName()) . ' TO ' . $db->q($name)
            ;
        }

    }
