<?php
    /**
    * @package   project/core
    * @version   1.0.0 23.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Column extends \Ada\Core\Proto {

        const
            DATA_TYPES_ALIASES  = [
                'binary'  => 'blob',
                'boolean' => 'tinyint(1)'
            ],
            DATA_TYPES_ARGS_QTY = [
                'bigint'  => 1,
                'decimal' => 2,
                'int'     => 1
            ];

        protected static
            $instances          = [];

        protected
            $after              = '',
            $charset            = '',
            $collation          = '',
            $default_value      = '',
            $init_params        = [],
            $is_auto_increment  = false,
            $is_nullable        = false,
            $name               = '',
            $primary_key        = '',
            $table              = null,
            $type               = 'int',
            $type_args          = [],
            $unique_key         = '';

        public static function init(
            \Ada\Core\Db\Table $table,
            string             $name   = '',
            bool               $cached = true
        ): \Ada\Core\Db\Column {
            $db  = $table->getDb();
            $res =&
                static::$instances
                [$db->getDriver()]
                [$db->getName()]
                [$db->getSchema()]
                [$table->getName()]
                [\Ada\Core\Clean::cmd($name)]
                ?? null;
            if ($name && $res && $cached) {
                return $res;
            }
            return $res = new static(...func_get_args());
        }

        protected function __construct(
            \Ada\Core\Db\Table $table,
            string             $name = ''
        ) {
            $this->name  = \Ada\Core\Clean::cmd($name);
            $this->table = $table;
            if (!$this->getName()) {
                return;
            }
            $this->init_params = $this->extractParams();
            if (!$this->init_params) {
                throw new \Ada\Core\Exception(
                    (
                        'No column \''   . $this->getNameInit()      . '\' ' .
                        'in table \''    . $table->getName()         . '\' ' .
                        'of database \'' . $this->getDb()->getName() . '\''
                    ),
                    1
                );
            }
            $this->setProps($this->init_params);
        }

        public function delete(): bool {
            if (!$this->exists()) {
                return true;
            }
            $error = (
                'Failed to delete column \'' . $this->init_params['name']   . '\' '  .
                'of table \''                . $this->getTable()->getName() . '\' '  .
                'of database \''             . $this->getDb()->getName()    . '\''
            );
            if (count($this->getTable()->getColumns()) == 1) {
                throw new \Ada\Core\Exception(
                    $error . '. Table must contain at least one column',
                    4
                );
            }
            try {
                $res = $this->getDb()->exec($this->getQueryDelete());
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    5
                );
            }
            if ($res) {
                $this->init_params = [];
            }
            return $res;
        }

        public function exists(): bool {
            return (bool) $this->init_params;
        }

        public function getAfter(): string {
            return $this->after;
        }

        public function getCharset(): string {
            return $this->charset;
        }

        public function getCollation(): string {
            return $this->collation;
        }

        public function getDb(): Driver {
            return $this->getTable()->getDb();
        }

        public function getDefaultValue() {
            return $this->default_value;
        }

        public function getIsAutoIncrement(): bool {
            return $this->is_auto_increment;
        }

        public function getIsNullable(): bool {
            return $this->is_nullable;
        }

        public function getName(): string {
            return $this->name;
        }

        public function getPrimaryKey(): string {
            return
                $this->primary_key === '1'
                    ? $this->getName()
                    : $this->primary_key;
        }

        public function getTable(): \Ada\Core\Db\Table {
            return $this->table;
        }

        public function getType(bool $real = false): string {
            return
                $real
                    ? $this->type
                    : static::DATA_TYPES_ALIASES[$this->type] ?? $this->type;
        }

        public function getTypeArgs(): array {
            return $this->type_args;
        }

        public function getUniqueKey(): string {
            return
                $this->unique_key === '1'
                    ? $this->getName()
                    : $this->unique_key;
        }

        public function save(): bool {
            $db    = $this->getDb();
            $props = array_intersect_key($this->getProps(), $this->init_params);
            if (
                $this->exists() &&
                !\Ada\Core\Arr::init($this->init_params)->diffRecursive($props)
            ) {
                return true;
            }
            $error = '
                Failed to save column ' .
                (!$this->getName() ? '' : '\'' . $this->getName() . '\'') . '
                of table \''            . $this->getTable()->getName()    . '\'
                of database \''         . $this->getDb()->getName()       . '\'
            ';
            if (!$this->getName()) {
                throw new \Ada\Core\Exception(
                    $error . '. Column name must not be empty',
                    2
                );
            }
            $method = 'getQueries' . ($this->exists() ? 'Update' : 'Create');
            $db->beginTransaction();
            try {
                foreach ($this->$method() as $query) {
                    $db->exec($query);
                }
            } catch (\Throwable $e) {
                $db->rollBackTransaction();
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    3
                );
            }
            $db->commitTransaction();
            $this->init_params = $this->extractParams();
            $this->setProps($this->init_params);
            return true;
        }

        public function setAfter(string $column_name) {
            if (!in_array($column_name, $this->getTable()->getColumns())) {
                throw new \Ada\Core\Exception(
                    (
                        'No column \''   . $column_name                 . '\' ' .
                        'in table \''    . $this->getTable()->getName() . '\' ' .
                        'of database \'' . $this->getDb()->getName()    . '\''
                    ),
                    1
                );
            }
            $this->after = \Ada\Core\Clean::cmd($column_name);
        }

        public function setCharset(string $charset) {
            $this->charset = \Ada\Core\Clean::cmd($charset);
        }

        public function setCollation(string $collation) {
            $this->collation = \Ada\Core\Clean::cmd($collation);
        }

        public function setDefaultValue(string $default_value) {
            $this->default_value = $default_value;
        }

        public function setIsAutoIncrement(bool $is_auto_increment) {
            $this->is_auto_increment = $is_auto_increment;
        }

        public function setIsNullable(bool $is_nullable) {
            $this->is_nullable = $is_nullable;
        }

        public function setName(string $name) {
            $this->name = \Ada\Core\Clean::cmd($name);
        }

        public function setPrimaryKey(string $primary_key) {
            $this->primary_key = \Ada\Core\Clean::cmd($primary_key);
        }

        public function setType(string $type) {
            $type       = \Ada\Core\Clean::cmd($type);
            $this->type = static::DATA_TYPES_ALIASES[$type] ?? $type;
            $this->setTypeArgs($this->getTypeArgs());
        }

        public function setTypeArgs() {
            $arg             = func_get_arg(0);
            $this->type_args = \Ada\Core\Clean::values(
                array_slice(
                    is_array($arg) ? $arg : func_get_args(),
                    0,
                    static::DATA_TYPES_ARGS_QTY[$this->getType()] ?? 0
                ),
                'int'
            );
        }

        public function setUniqueKey(string $unique_key) {
            $this->unique_key = \Ada\Core\Clean::cmd($unique_key);
            if ($this->unique_key === '1') {
                $this->unique_key = true;
            }
        }

        protected function extractParams(): array {
            $db    = $this->getDb();
            $table = $this->getTable();
            $row   = $db->fetchRow('
                SELECT ' . $db->qs([
                    'CHARACTER_SET_NAME',
                    'COLLATION_NAME',
                    'COLUMN_DEFAULT',
                    'COLUMN_NAME',
                    'COLUMN_TYPE',
                    'DATA_TYPE',
                    'EXTRA',
                    'IS_NULLABLE',
                    'TABLE_NAME',
                    'TABLE_SCHEMA'
                ]) . '
                FROM '   . $db->q('information_schema.COLUMNS') . '
                WHERE '  . $db->q('TABLE_SCHEMA') . ' LIKE ' . $db->e($table->getSchema()) . '
                AND '    . $db->q('TABLE_NAME')   . ' LIKE ' . $db->e($table->getName(true, false)) . '
                AND '    . $db->q('COLUMN_NAME')  . ' LIKE ' . $db->e($this->getName())
            );
            if (!$row) {
                return [];
            }
            $type_args = [];
            preg_match('/\((.*)\)/', $row['COLUMN_TYPE'], $type_args);
            $res       = [
                'charset'           => trim($row['CHARACTER_SET_NAME']),
                'collation'         => trim($row['COLLATION_NAME']),
                'default_value'     => trim($row['COLUMN_DEFAULT']),
                'is_auto_increment' => strpos(strtolower($row['EXTRA']), 'auto_increment') !== false,
                'is_nullable'       => strtolower(trim($row['IS_NULLABLE'])) == 'yes',
                'name'              => trim($row['COLUMN_NAME']),
                'primary_key'       => '',
                'type'              => strtolower(trim($row['DATA_TYPE'])),
                'type_args'         => \Ada\Core\Clean::values(
                    explode(',', end($type_args)),
                    'int'
                ),
                'unique_key'        => ''
            ];
            foreach ($db->fetchRows('
                SHOW INDEX
                FROM '  . $db->t($table->getName()) . '
                WHERE ' . $db->q('Column_name') . ' LIKE ' . $db->e($this->getName()) . '
                AND '   . $db->q('Non_unique')  . ' = 0'
            ) as $index) {
                $key = trim($index['Key_name']);
                $res[
                    strtolower($key) == 'primary'
                        ? 'primary_key'
                        : 'unique_key'
                ] = $key;
            }
            return $res;
        }

        protected function getQueriesCreate(): array {
            $res   = [];
            $res[] = $this->getQueryCreate();
            if ($this->getPrimaryKey()) {
                $res[] = $this->getQueryAddPrimaryKey();
            }
            if ($this->getUniqueKey()) {
                $res[] = $this->getQueryAddUniqueKey();
            }
            return $res;
        }

        protected function getQueriesUpdate(): array {
            $res = [];
            if ($this->getName() != $this->init_params['name']) {
                $res[] = $this->getQueryRename();
            }
            if ($this->getPrimaryKey() && !$this->init_params['primary_key']) {
                $res[] = $this->getQueryAddPrimaryKey();
            }
            elseif(!$this->getPrimaryKey() && $this->init_params['primary_key']) {
                $res[] = $this->getQueryDropPrimaryKey();
            }
            if ($this->getUniqueKey() && !$this->init_params['unique_key']) {
                $res[] = $this->getQueryAddUniqueKey();
            }
            elseif(!$this->getUniqueKey() && $this->init_params['unique_key']) {
                $res[] = $this->getQueryDropUniqueKey();
            }
            $res[] = $this->getQueryUpdate();
            return $res;
        }

        protected function getQueryAddPrimaryKey(): string {
            $db = $this->getDb();
            return '
                ALTER TABLE '    . $db->t($this->getTable()->getName()) . '
                ADD CONSTRAINT ' . $db->q($this->getPrimaryKey()) . '
                PRIMARY KEY ('   . $db->q($this->getName()) . ')
            ';
        }

        protected function getQueryAddUniqueKey(): string {
            $db = $this->getDb();
            return '
                ALTER TABLE '    . $db->t($this->getTable()->getName()) . '
                ADD CONSTRAINT ' . $db->q($this->getUniqueKey()) . '
                UNIQUE ('        . $db->q($this->getName()) . ')
            ';
        }

        protected function getQueryCreate(): string {
            return '
                ALTER TABLE ' . $this->getDb()->t($this->getTable()->getName()) . '
                ADD '         . $this->getQueryCreateUpdate();
        }

        public function getQueryCreateUpdate(): string {
            $db = $this->getDb();
            return (
                $db->q($this->getName()) . ' ' .
                $this->getQuerySetType() .
                (
                    !$this->getCharset()
                        ? ''
                        : ' CHARACTER SET ' . $db->e($this->getCharset())
                ) .
                (
                    !$this->getCollation()
                        ? ''
                        : ' COLLATE ' . $db->e($this->getCollation())
                ) .
                (
                    $this->getDefaultValue() === ''
                        ? ''
                        : ' DEFAULT \'' . $this->getDefaultValue() . '\''
                ) .
                (
                    ($this->getIsNullable() ? '' : ' NOT') . ' NULL'
                ) .
                (
                    $this->getIsAutoIncrement() ? ' AUTO_INCREMENT' : ''
                ) .
                (
                    !$this->getAfter()
                        ? ''
                        : ' AFTER ' . $db->q($this->getAfter())
                )
            );
        }

        protected function getQueryDelete(): string {
            $db = $this->getDb();
            return '
                ALTER TABLE ' . $db->t($this->getTable()->getName()) . '
                DROP COLUMN ' . $db->q($this->init_params['name']);
        }

        protected function getQueryDropPrimaryKey(): string {
            return '
                ALTER TABLE ' . $this->getDb()->t($table->getName()) . '
                DROP PRIMARY KEY
            ';
        }

        protected function getQueryDropUniqueKey(): string {
            $db = $this->getDb();
            return '
                ALTER TABLE ' . $db->t($table->getName()) . '
                DROP KEY '    . $db->q($this->init_params['unique_key']);
        }

        protected function getQueryRename(): string {
            $db = $this->getDb();
            return '
                ALTER TABLE ' . $db->t($this->getTable()->getName()) . '
                CHANGE '      . $db->q($this->init_params['name'])   . ' ' .
                                $db->q($this->getName())             . ' ' .
                                $this->getQuerySetType();
        }

        protected function getQuerySetType(): string {
            return
                $this->getType(true) .
                (
                    $this->getTypeArgs()
                        ? ('(' . implode(', ', $this->getTypeArgs()) . ')')
                        : ''
                );
        }

        protected function getQueryUpdate(): string {
            return '
                ALTER TABLE ' . $this->getDb()->t($this->getTable()->getName()) . '
                MODIFY '      . $this->getQueryCreateUpdate();
        }

    }
