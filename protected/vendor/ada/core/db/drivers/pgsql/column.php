<?php
    /**
    * @package   project/core
    * @version   1.0.0 13.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Column extends \Ada\Core\Db\Column {

        public static function create($table, array $params): self {
            $db                = $table->getDb();
            $defaults          = get_class_vars(__CLASS__);
            $collation         = \Ada\Core\Clean::cmd(
                $params['collation']         ?? $defaults['collation']
            );
            $default_value     = \Ada\Core\Type::set(trim(
                $params['default_value']     ?? $defaults['default_value']
            ));
            $is_auto_increment = \Ada\Core\Clean::bool(
                $params['is_auto_increment'] ?? $defaults['is_auto_increment']
            );
            $is_nullable       = \Ada\Core\Clean::bool(
                $params['is_nullable']       ?? $defaults['is_nullable']
            );
            $is_primary_key    = \Ada\Core\Clean::bool(
                $params['is_primary_key']    ?? $defaults['is_primary_key']
            );
            $is_unique_key     = \Ada\Core\Clean::bool(
                $params['is_primary_key']    ?? $defaults['is_unique_key']
            );
            $length            = \Ada\Core\Clean::int(
                $params['length']            ?? $defaults['length']
            );
            $name              = \Ada\Core\Clean::cmd(
                $params['name']              ?? $defaults['name']
            );
            $type              = preg_replace(
                '/[^ a-z0-9_\.-]/i',
                '',
                trim($params['type'] ?? $defaults['type'])
            );
            $error             = (
                'Failed to add column ' .
                (!$name ? '' : '\'' . $name . '\'') .
                ' to table \'' . $table->getName() . '\''
            );
            if (!$name) {
                throw new \Ada\Core\Exception(
                    $error . '. Column name must not be empty',
                    1
                );
            }
            $queries           = [];
            $queries[]         = '
                ALTER TABLE ' . $db->t($table->getName()) . '
                ADD '         . $db->q($name) . ' ' .
                (
                    $is_auto_increment
                        ? 'serial'
                        : (
                            $type .
                            (
                                !$length
                                    ? ''
                                    : '(' . $db->e($length) . ')'
                            ) .
                            (
                                $default_value === ''
                                    ? ''
                                    : ' DEFAULT \'' . $default_value . '\''
                            ) .
                            (
                                !$collation
                                    ? ''
                                    : ' COLLATE ' . $db->e($collation)
                            )
                        )
                ) .
                (
                    ($is_nullable ? '' : ' NOT') . ' NULL'
                );
            if ($is_primary_key) {
                $queries[] = '
                    ALTER TABLE '      . $db->t($table->getName()) . '
                    ADD PRIMARY KEY (' . $db->q($name) . ')
                ';
            }
            if ($is_unique_key) {
                $queries[] = '
                    ALTER TABLE ' . $db->t($table->getName()) . '
                    ADD UNIQUE (' . $db->q($name) . ')
                ';
            }
            try {
                foreach ($queries as $query) {
                    $db->exec($query);
                }
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    2
                );
            }
            return $table->getColumn($name, false);
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
                WHERE '      . $db->q('c.table_name')        . ' LIKE ' . $db->e($table->getName(true)) . '
                AND '        . $db->q('c.column_name')       . ' LIKE ' . $db->e($this->getName())
            );
            if (!$row) {
                return [];
            }
            $constraint_type = strtolower(trim($row['constraint_type']));
            $res             = [
                'collation'      => trim($row['collation_name']),
                'default_value'  => $row['column_default'],
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

    }
