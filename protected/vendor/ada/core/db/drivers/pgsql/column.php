<?php
    /**
    * @package   project/core
    * @version   1.0.0 03.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Column extends \Ada\Core\Db\Column {

        const
            DATA_TYPES          = [
                'binary'  => 'blob',
            ],
            DATA_TYPES_ARGS_QTY = [
                'decimal' => 2
            ];

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
                $res[$key] = strtolower(trim($index['constraint_name']));
            }
            return $res;
        }

        protected function getQueryCreate(): string {
            $db = $this->getDb();
            return '
                ALTER TABLE ' . $db->t($this->getTable()->getName()) . '
                ADD '         . $db->q($this->getName()) . ' ' .
                (
                    $this->getIsAutoIncrement()
                        ? 'serial'
                        : (
                            $this->getQuerySetType() .
                            (
                                $this->getDefaultValue() === ''
                                    ? ''
                                    : ' DEFAULT \'' . $this->getDefaultValue() . '\''
                            ) .
                            (
                                !$this->getCollation()
                                    ? ''
                                    : ' COLLATE ' . $this->getCollation()
                            )
                        )
                ) .
                (
                    ($this->getIsNullable() ? '' : ' NOT') . ' NULL'
                );
        }

        protected function getQueryDropPrimaryKey(): string {
            return '
                ALTER TABLE '     . $db->t($this->getTable()->getName()) . '
                DROP CONSTRAINT ' . $db->q($this->init_params['primary_key']);
        }

        protected function getQueryDropUniqueKey(): string {
            $db = $this->getDb();
            return '
                ALTER TABLE '     . $db->t($this->getTable()->getName()) . '
                DROP CONSTRAINT ' . $db->q($this->init_params['unique_key']);
        }

        protected function getQueryRename(): string {
            $db = $this->getDb();
            return '
                ALTER TABLE '   . $db->t($this->getTable()->getName()) . '
                RENAME COLUMN ' . $db->q($this->init_params['name']) . '
                TO '            . $db->q($this->getName())
            ;
        }

        protected function getQueryUpdate(): string {
            $res   = '';
            $db    = $this->getDb();
            $table = $this->getTable();
            if (
                $this->getType()      != $this->init_params['type'] ||
                $this->getTypeArgs()  != $this->init_params['type_args'] ||
                $this->getCollation() != $this->init_params['collation']
            ) {
                $res .= '
                    TYPE ' . $this->getQuerySetType() .
                    (
                        !$this->getCollation()
                            ? ''
                            : ' COLLATE ' . $this->getCollation()
                    ) . '
                    USING (' . $table->getName() . '::' . $this->getType() . ')
                ';
            }
            if ($this->getDefaultValue() != $this->init_params['default_value']) {
                $res .= (
                    $this->getDefaultValue() === ''
                        ? 'DROP DEFAULT'
                        : 'SET DEFAULT \'' . $this->getDefaultValue() . '\''
                );
            }
            if ($this->getIsNullable() != $this->init_params['is_nullable']) {
                $res .= ($this->getIsNullable() ? ' DROP' : ' SET') . ' NOT NULL';
            }
            if ($this->getIsAutoIncrement() != $this->init_params['is_auto_increment']) {
                $res .= (
                    !$this->getIsAutoIncrement()
                        ? 'DROP DEFAULT'
                        : '
                            SET DEFAULT nextval(\'' .
                                $table->getName(true, false) . '_' .
                                $this->getName() .
                                '_seq' .
                            '\')
                        '
                );
            }
            return
                !$res
                    ? $res
                    : '
                        ALTER TABLE '  . $db->t($table->getName()) . '
                        ALTER COLUMN ' . $db->q($this->getName()) .
                        $res;
        }

    }
