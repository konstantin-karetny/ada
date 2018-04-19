<?php
    /**
    * @package   project/core
    * @version   1.0.0 19.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Column extends \Ada\Core\Proto {

        const
            DATA_TYPES          = [
                'bigint'  => 'bigint',
                'binary'  => 'blob',
                'boolean' => 'tinyint(1)',
                'decimal' => 'decimal',
                'int'     => 'int'
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
            $type_real          = '',
            $unique_key         = '';

        public static function create($table, array $params): self {
            $error  = (
                'Failed to create column ' .
                (!$params['name'] ? '' : '\'' . $params['name'] . '\'') .
                ' in table \'' . $table->getName() . '\''
            );
            if (!isset($params['name'])) {
                throw new \Ada\Core\Exception(
                    $error . '. Column name is required',
                    1
                );
            }
            try {
                $params = static::preapreParams($params);
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    $e->getCode()
                );
            }
            $db = $table->getDb();
            try {
                foreach (static::getCreateQueries($table, $params) as $query) {
                    $db->exec($query);
                }
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    3
                );
            }
            return $table->getColumn($params['name'], false);
        }

        public static function getCreateQuery($db, array $params): string {
            return (
                $db->q($params['name']) . ' ' .
                static::getTypeQuery($params) .
                (
                    !$params['charset']
                        ? ''
                        : ' CHARACTER SET ' . $db->e($params['charset'])
                ) .
                (
                    !$params['collation']
                        ? ''
                        : ' COLLATE ' . $db->e($params['collation'])
                ) .
                (
                    $params['default_value'] === ''
                        ? ''
                        : ' DEFAULT \'' . $params['default_value'] . '\''
                ) .
                (
                    ($params['is_nullable'] ? '' : ' NOT') . ' NULL'
                ) .
                (
                    $params['is_auto_increment'] ? ' AUTO_INCREMENT' : ''
                ) .
                (
                    !$params['after']
                        ? ''
                        : ' AFTER ' . $db->q($params['after'])
                )
            );
        }

        public static function init(
            string $name,
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
            if ($cached && $res) {
                return $res;
            }
            return $res = new static(...func_get_args());
        }

        public static function preapreParams(array $params): array {
            if (isset($params['name']) && !$params['name']) {
                throw new \Ada\Core\Exception('Column name is required', 1);
            }
            if (
                 isset($params['type']) &&
                !isset(static::DATA_TYPES[$params['type']])
            ) {
                throw new \Ada\Core\Exception(
                    'Unknown type \'' . $params['type'] . '\'',
                    2
                );
            }
            foreach (array_merge(
                get_class_vars(__CLASS__),
                [
                    'after' => ''
                ]
            ) as $k => $v) {
                $params[$k] = $params[$k] ?? $v;
                switch ($k) {
                    case 'after'             :
                    case 'charset'           :
                    case 'collation'         :
                    case 'name'              :
                        $params[$k] = \Ada\Core\Clean::cmd($params[$k]);
                        break;
                    case 'default_value'     :
                        $params[$k] = trim($params[$k]);
                        break;
                    case 'is_auto_increment' :
                    case 'is_nullable'       :
                        $params[$k] = \Ada\Core\Clean::bool($params[$k]);
                        break;
                    case 'primary_key'       :
                    case 'unique_key'        :
                        $params[$k] = (
                            $params[$k] === true
                                ? $params['name']
                                : \Ada\Core\Clean::cmd($params[$k])
                        );
                        break;
                    case 'type'              :
                        $params[$k] = preg_replace(
                            '/[^ a-z0-9_\.-]/i',
                            '',
                            trim($params[$k])
                        );
                        break;
                    case 'type_args'         :
                        $params[$k] = \Ada\Core\Clean::values(
                            (
                                is_array($params[$k])
                                    ? $params[$k]
                                    : \Ada\Core\Type::set($params[$k], 'array')
                            ),
                            'int'
                        );
                        break;
                    default                  :
                        unset($params[$k]);
                }
            }
            $params['type_args'] = array_slice(
                $params['type_args'],
                0,
                static::DATA_TYPES_ARGS_QTY[$params['type']] ?? 0
            );
            return $params;
        }

        public function __construct(
            string $name,
            Table  $table,
            bool   $cached = true
        ) {
            $this->name  = \Ada\Core\Clean::cmd($name);
            $this->table = $table;
            $props       = $this->extractProps();
            if (!$props) {
                throw new \Ada\Core\Exception(
                    (
                        'No column \''   . $this->getName()          . '\' ' .
                        'in table \''    . $table->getName()         . '\' ' .
                        'of database \'' . $this->getDb()->getName() . '\''
                    ),
                    4
                );
            }
            $this->setProps($props);
        }

        public function delete(): bool {
            try {
                return $this->getDb()->exec($this->getDeleteQuery());
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    (
                        'Failed to delete column \'' . $this->getName() . '\' ' .
                        'of table \'' .    $this->getTable()->getName() . '\'. ' .
                        $e->getMessage()
                    ),
                    6
                );
            }
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

        public function getType(): string {
            return $this->type;
        }

        public function getTypeArgs(): array {
            return $this->type_args;
        }

        public function getTypeReal(): string {
            return $this->type_real;
        }

        public function getUniqueKey(): string {
            return $this->unique_key;
        }

        public function update(array $params): bool {
            $db     = $this->getDb();
            $table  = $this->getTable();
            $params = array_merge($this->getProps(['table']), $params);
            $error  = (
                'Failed to update column \'' . $this->getName() . '\' ' .
                'of table \'' . $table->getName() . '\''
            );
            try {
                $params = static::preapreParams($params);
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    $e->getCode()
                );
            }
            $db->beginTransaction();
            try {
                foreach ($this->getUpdateQueries($params) as $query) {
                    $db->exec($query);
                }
            } catch (\Throwable $e) {
                $db->rollBackTransaction();
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    5
                );
            }
            $db->commitTransaction();
            $table->getColumn($params['name'], false);
            return true;
        }

        protected function extractProps(): array {
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
            preg_match('/\((.*)\)/', $row['COLUMN_TYPE'], $type_args);
            $res = [
                'charset'           => trim($row['CHARACTER_SET_NAME']),
                'collation'         => trim($row['COLLATION_NAME']),
                'default_value'     => trim($row['COLUMN_DEFAULT']),
                'is_auto_increment' => strpos(strtolower($row['EXTRA']), 'auto_increment') !== false,
                'is_nullable'       => strtolower(trim($row['IS_NULLABLE'])) == 'yes',
                'type_args'         => \Ada\Core\Clean::values(
                    explode(',', end($type_args)),
                    'int'
                ),
                'type_real'         => strtolower(trim($row['DATA_TYPE']))
            ];
            $res['type'] = array_search($res['type_real'], static::DATA_TYPES);
            if ($res['type'] === false) {
                $res['type'] = $res['type_real'];
            }
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

        protected static function getCreateQueries(
                  $table,
            array $params
        ): array {
            $res   = [];
            $db    = $table->getDb();
            $res[] = '
                ALTER TABLE ' . $db->t($table->getName()) . '
                ADD '         . static::getCreateQuery($db, $params)
            ;
            if ($params['primary_key']) {
                $res[] = '
                    ALTER TABLE '      . $db->t($table->getName()) . '
                    ADD CONSTRAINT '   . $db->q($params['primary_key']) . '
                    ADD PRIMARY KEY (' . $db->q($params['name']) . ')
                ';
            }
            if ($params['unique_key']) {
                $res[] = '
                    ALTER TABLE '      . $db->t($table->getName()) . '
                    ADD CONSTRAINT '   . $db->q($params['unique_key']) . '
                    ADD UNIQUE ('      . $db->q($params['name']) . ')
                ';
            }
            return $res;
        }

        protected static function getTypeQuery(array $params): string {
            return (
                static::DATA_TYPES[$params['type']] .
                (
                    (
                        $params['type_args'] &&
                        isset(static::DATA_TYPES[$params['type']])
                    )
                        ? ('(' . implode(', ', $params['type_args']) . ')')
                        : ''
                )
            );
        }

        protected function getDeleteQuery(): string {
           $db = $this->getDb();
            return '
                ALTER TABLE ' . $db->t($this->getTable()->getName()) . '
                DROP COLUMN ' . $db->q($this->getName())
            ;
        }

        protected function getRenameQuery(array $params): string {
            $db = $this->getDb();
            return '
                ALTER TABLE ' . $db->t($this->getTable()->getName()) . '
                CHANGE ' .      $db->q($this->getName()) . ' ' .
                                $db->q($params['name'])  . ' ' .
                static::getTypeQuery($params)
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
                            ALTER TABLE '    . $db->t($table->getName()) . '
                            ADD CONSTRAINT ' . $db->q($params['primary_key']) . '
                            PRIMARY KEY ('   . $db->q($params['name']) . ')
                        '
                        : '
                            ALTER TABLE '    . $db->t($table->getName()) . '
                            DROP PRIMARY KEY
                        ';
            }
            if ($params['unique_key'] != $this->getUniqueKey()) {
                $res[] =
                    $params['unique_key']
                        ? '
                            ALTER TABLE '    . $db->t($table->getName()) . '
                            ADD CONSTRAINT ' . $db->q($params['unique_key']) . '
                            ADD UNIQUE ('    . $db->q($params['name']) . ')
                        '
                        : '
                            ALTER TABLE '    . $db->t($table->getName()) . '
                            DROP KEY '       . $db->q($this->getUniqueKey())
                        ;
            }
            $res[] = '
                ALTER TABLE ' . $db->t($table->getName()) . '
                MODIFY '      . static::getCreateQuery($db, $params)
            ;
            return $res;
        }

    }
