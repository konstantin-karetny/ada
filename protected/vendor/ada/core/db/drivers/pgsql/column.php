<?php
    /**
    * @package   project/core
    * @version   1.0.0 19.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Column extends \Ada\Core\Db\Column {

        const
            DATA_TYPES          = [
                'bigint'  => 'bigint',
                'binary'  => 'blob',
                'boolean' => 'boolean',
                'decimal' => 'decimal',
                'int'     => 'integer'
            ],
            DATA_TYPES_ARGS_QTY = [
                'decimal' => 2
            ];

        public static function getCreateQuery($db, array  $params): string {
            return (
                $db->q($params['name'])   . ' ' .
                (
                    $params['is_auto_increment']
                        ? 'serial'
                        : (
                            static::getTypeQuery($params) .
                            (
                                $params['default_value'] === ''
                                    ? ''
                                    : ' DEFAULT \'' . $params['default_value'] . '\''
                            ) .
                            (
                                !$params['collation']
                                    ? ''
                                    : ' COLLATE ' . $params['collation']
                            )
                        )
                ) .
                (
                    ($params['is_nullable']? '' : ' NOT') . ' NULL'
                )
            );
        }

        protected function extractProps(): array {
            $table = $this->getTable();
            $db    = $table->getDb();
            $row   = $db->fetchRow('
                SELECT ' . $db->qs([
                    'character_maximum_length',
                    'character_set_name',
                    'collation_name',
                    'column_default',
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
                )
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
            foreach ($db->fetchRows('
                SELECT ' . $db->qs([
                    'tc.constraint_name',
                    'tc.constraint_type'
                ]) . '
                FROM '   . $db->q('information_schema.key_column_usage',  'cu') . '
                JOIN '   . $db->q('information_schema.table_constraints', 'tc') . '
                ON '     . $db->q('cu.table_schema')    . ' = '    . $db->q('tc.table_schema') . '
                AND '    . $db->q('cu.table_name')      . ' = '    . $db->q('tc.table_name') . '
                AND '    . $db->q('cu.constraint_name') . ' = '    . $db->q('tc.constraint_name') . '
                WHERE '  . $db->q('cu.table_schema')    . ' LIKE ' . $db->e($table->getSchema()) . '
                AND '    . $db->q('cu.table_name')      . ' LIKE ' . $db->e($table->getName(true, false)) . '
                AND '    . $db->q('cu.column_name')     . ' LIKE ' . $db->e($this->getName())
            ) as $index) {
                switch (strtolower(trim($index['constraint_type']))) {
                    case 'primary key':
                        $key = 'primary_key';
                        break;
                    case 'unique':
                        $key = 'unique_key';
                        break;
                    default:
                        continue 2;
                }
                $res['is_' . $key]    = true;
                $res[$key  . '_name'] = strtolower(trim($index['constraint_name']));
            }
            return $res;
        }

        protected function getRenameQuery(array $params): string {
            $db = $this->getDb();
            return '
                ALTER TABLE '   . $db->t($this->getTable()->getName()) . '
                RENAME COLUMN ' . $db->q($this->getName()) . '
                TO '            . $db->q($params['name'])
            ;
        }

        protected function getUpdateQueries(array $params): array {
            $res   = [];
            $db    = $this->getDb();
            $table = $this->getTable();
            if ($params['name'] != $this->getName()) {
                $res[] = $this->getRenameQuery($params);
            }
            if ($params['primary_key'] != $this->getPrimaryKey()) {
                $res[] =
                    $params['primary_key']
                        ? '
                            ALTER TABLE '      . $db->t($table->getName()) . '
                            ADD CONSTRAINT '   . $db->q($params['primary_key']) . '
                            ADD PRIMARY KEY (' . $db->q($params['name']) . ')
                        '
                        : '
                            ALTER TABLE '      . $db->t($table->getName()) . '
                            DROP CONSTRAINT '  . $db->q($this->getPrimaryKey())
                        ;
            }
            if ($params['unique_key'] != $this->getUniqueKey()) {
                $res[] =
                    $params['unique_key']
                        ? '
                            ALTER TABLE '      . $db->t($table->getName()) . '
                            ADD CONSTRAINT '   . $db->q($params['unique_key']) . '
                            ADD UNIQUE ('      . $db->q($params['name']) . ')
                        '
                        : '
                            ALTER TABLE '      . $db->t($table->getName()) . '
                            DROP CONSTRAINT '  . $db->q($this->getUniqueKey())
                        ;
            }
            $alter_table = '
                ALTER TABLE '  . $db->t($table->getName()) . '
                ALTER COLUMN ' . $db->q($params['name'])   . '
            ';
            if (
                $params['type']      != $this->getType() ||
                $params['type_args'] != $this->getTypeArgs() ||
                $params['collation'] != $this->getCollation()
            ) {
                $res[] = (
                    $alter_table . '
                    TYPE ' . static::getTypeQuery($params) .
                    (
                        !$params['collation']
                            ? ''
                            : ' COLLATE ' . $params['collation']
                    ) .
                    ' USING (' . $params['name'] . '::' . $params['type'] . ')'
                );
            }
            if ($params['default_value'] != $this->getDefaultValue()) {
                $res[] = (
                    $alter_table .
                    (
                        $params['default_value'] === ''
                            ? 'DROP DEFAULT'
                            : 'SET DEFAULT \'' . $params['default_value'] . '\''
                    )
                );
            }
            if ($params['is_nullable'] != $this->getIsNullable()) {
                $res[] = (
                    $alter_table .
                    ($params['is_nullable'] ? ' DROP' : ' SET') . ' NOT NULL'
                );
            }
            if ($params['is_auto_increment'] != $this->getIsAutoIncrement()) {
                $res[] = (
                    $alter_table .
                    (
                        !$params['is_auto_increment']
                            ? 'DROP DEFAULT'
                            : (
                                'SET DEFAULT nextval(\'' .
                                    $table->getName(true, false) . '_' .
                                    $params['name'] .
                                    '_seq' .
                                '\')'
                            )
                    )
                );
            }
            return $res;
        }

    }
