<?php
    /**
    * @package   project/core
    * @version   1.0.0 20.04.2018
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
            $charset            = '',
            $collation          = '',
            $default_value      = '',
            $is_auto_increment  = false,
            $is_nullable        = false,
            $name               = '',
            $primary_key        = '',
            $table              = null,
            $type               = 'int',
            $type_args          = [],
            $unique_key         = '';

        public static function init(
            string $name = '',
            Table  $table,
            bool   $cached = true
        ) {
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

        public function __construct(
            string $name = '',
            Table  $table,
            bool   $cached = true
        ) {
            $this->name  = \Ada\Core\Clean::cmd($name);
            $this->table = $table;
            if (!$this->getName()) {
                return;
            }
            $params = $this->extractParams();
            if (!$params) {
                throw new \Ada\Core\Exception(
                    (
                        'No column \''   . $this->getName()          . '\' ' .
                        'in table \''    . $table->getName()         . '\' ' .
                        'of database \'' . $this->getDb()->getName() . '\''
                    ),
                    1
                );
            }
            $this->setProps($params);
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
            return $this->primary_key;
        }

        public function getTable(): Table {
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
            return $this->unique_key;
        }

        public function save(): bool {
            $props = $this->extractParams();
            $action = $props ? 'update' : 'create';


            try {
                foreach ($this->{'getQueries' . ucfirst($action)}($params) as $query) {
                    $db->exec($query);
                }
            } catch (\Throwable $e) {
                $db->rollBackTransaction();
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    5
                );
            }

            exit(var_dump( $props ));
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
        }

        protected function extractParams(): array {
            $table = $this->getTable();
            $db    = $this->getDb();
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
                'type'              => strtolower(trim($row['DATA_TYPE'])),
                'type_args'         => \Ada\Core\Clean::values(
                    explode(',', end($type_args)),
                    'int'
                ),
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

    }
