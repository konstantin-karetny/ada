<?php
    /**
    * @package   project/core
    * @version   1.0.0 18.04.2018
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
                                    : ' COLLATE ' . $params['collation']
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
                SELECT ' . $db->qs([
                    'character_maximum_length',
                    'character_set_name',
                    'collation_name',
                    'column_default',
                    'data_type',
                    'is_nullable'
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
                'length'        => (int) $row['character_maximum_length'],
                'type'          => trim($row['data_type'])
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

        protected function getRenameQuery(string $name): string {
            $db = $this->getDb();
            return '
                ALTER TABLE '   . $db->t($this->getTable()->getName()) . '
                RENAME COLUMN ' . $db->q($this->getName()) . ' TO ' . $db->q($name)
            ;
        }

        protected function getUpdateQueries(array $params): array {
            $res   = [];
            $db    = $this->getDb();
            $table = $this->getTable();
            if ($params['name'] != $this->getName()) {
                $res[] = $this->getRenameQuery($params['name']);
            }
            if ($params['is_primary_key'] != $this->getIsPrimaryKey()) {
                $res[] =
                    $params['is_primary_key']
                        ? '
                            ALTER TABLE '      . $db->t($table->getName()) . '
                            ADD PRIMARY KEY (' . $db->q($params['name']) . ')
                        '
                        : '
                            ALTER TABLE '     . $db->t($table->getName()) . '
                            DROP CONSTRAINT ' . $db->q($this->getPrimaryKeyName())
                        ;
            }
            if ($params['is_unique_key'] != $this->getIsUniqueKey()) {
                $res[] =
                    $params['is_unique_key']
                        ? '
                            ALTER TABLE ' . $db->t($table->getName()) . '
                            ADD UNIQUE (' . $db->q($params['name']) . ')
                        '
                        : '
                            ALTER TABLE ' . $db->t($table->getName()) . '
                            DROP CONSTRAINT '    . $db->q($this->getUniqueKeyName())
                        ;
            }
            $alter_table = '
                ALTER TABLE '  . $db->t($table->getName()) . '
                ALTER COLUMN ' . $db->q($params['name'])   . '
            ';
            if (
                $params['type']      != $this->getType() ||
                $params['length']    != $this->getLength() ||
                $params['collation'] != $this->getCollation()
            ) {
                $res[] = (
                    $alter_table . '
                    TYPE ' . $params['type'] .
                    (
                        !$params['length']
                            ? ''
                            : '(' . $db->e($params['length']) . ')'
                    ) .
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
