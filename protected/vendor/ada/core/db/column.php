<?php
    /**
    * @package   project/core
    * @version   1.0.0 18.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Column extends \Ada\Core\Proto {

        protected static
            $instances         = [];

        protected
            $charset           = '',
            $collation         = '',
            $default_value     = '',
            $is_auto_increment = false,
            $is_nullable       = false,
            $is_primary_key    = false,
            $is_unique_key     = false,
            $length            = 0,
            $name              = '',
            $primary_key_name  = '',
            $table             = null,
            $type              = 'int',
            $unique_key_name   = '';

        public static function create($table, array $params): self {
            $params = static::preapreParams($params);
            $error  = (
                'Failed to create column ' .
                (!$params['name'] ? '' : '\'' . $params['name'] . '\'') .
                ' in table \'' . $table->getName() . '\''
            );
            if (!$params['name']) {
                throw new \Ada\Core\Exception(
                    $error . '. Column name must not be empty',
                    1
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
                    2
                );
            }
            return $table->getColumn($params['name'], false);
        }

        public static function getCreateQuery($db, array $params): string {
            return (
                $db->q($params['name']) . ' ' .
                $params['type'] .
                (
                    !$params['length']
                        ? ''
                        : '(' . $db->e($params['length']) . ')'
                ) .
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
                    case 'is_primary_key'    :
                    case 'is_unique_key'     :
                        $params[$k] = \Ada\Core\Clean::bool($params[$k]);
                        break;
                    case 'length'            :
                        $params[$k] = \Ada\Core\Clean::int($params[$k]);
                        break;
                    case 'type'              :
                        $params[$k] = preg_replace(
                            '/[^ a-z0-9_\.-]/i',
                            '',
                            trim($params[$k])
                        );
                        break;
                    default                  :
                        unset($params[$k]);
                }
            }
            return $params;
        }

        public function __construct(
            string $name,
            Table  $table,
            bool   $cached = true
        ) {
            $this->name  = \Ada\Core\Clean::cmd($name);
            $this->table = $table;
            $props       = $this->getProps();
            if (!$props) {
                throw new \Ada\Core\Exception(
                    (
                        'No column \''   . $this->getName()          . '\' ' .
                        'in table \''    . $table->getName()         . '\' ' .
                        'of database \'' . $this->getDb()->getName() . '\''
                    ),
                    3
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
                    5
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

        public function getIsPrimaryKey(): bool {
            return $this->is_primary_key;
        }

        public function getIsUniqueKey(): bool {
            return $this->is_unique_key;
        }

        public function getLength() {
            return $this->length;
        }

        public function getName(): string {
            return $this->name;
        }

        public function getPrimaryKeyName(): string {
            return $this->primary_key_name;
        }

        public function getTable(): Table {
            return $this->table;
        }

        public function getType(): string {
            return $this->type;
        }

        public function getUniqueKeyName(): string {
            return $this->unique_key_name;
        }

        public function update(array $params): bool {
            foreach (static::preapreParams([]) as $k => $v) {
                $getter     = 'get' . \Ada\Core\Str::toCamelCase($k);
                $params[$k] = (
                    $params[$k]
                        ?? (
                            method_exists($this, $getter)
                                ? $this->$getter()
                                : (
                                    property_exists($this, $k)
                                        ? $this->$k
                                        : $v
                                )
                        )
                );
            }
            $db    = $this->getDb();
            $table = $this->getTable();
            $db->beginTransaction();
            try {
                foreach ($this->getUpdateQueries($params) as $query) {
                    $db->exec($query);
                }
            } catch (\Throwable $e) {
                $db->rollBackTransaction();
                throw new \Ada\Core\Exception(
                    (
                        'Failed to update column \'' . $this->getName() . '\' ' .
                        'of table \'' . $table->getName() . '\'. ' .
                        $e->getMessage()
                    ),
                    4
                );
            }
            $db->commitTransaction();
            $table->getColumn($params['name'], false);
            return true;
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
            if ($params['is_primary_key']) {
                $res[] = '
                    ALTER TABLE '      . $db->t($table->getName()) . '
                    ADD PRIMARY KEY (' . $db->q($params['name']) . ')
                ';
            }
            if ($params['is_unique_key']) {
                $res[] = '
                    ALTER TABLE ' . $db->t($table->getName()) . '
                    ADD UNIQUE (' . $db->q($params['name']) . ')
                ';
            }
            return $res;
        }

        protected function getDeleteQuery(): string {
           $db = $this->getDb();
            return '
                ALTER TABLE ' . $db->t($this->getTable()->getName()) . '
                DROP COLUMN ' . $db->q($this->getName())
            ;
        }

        protected function getProps(): array {
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
            preg_match('/\((.*)\)/', $row['COLUMN_TYPE'], $length);
            $res = [
                'charset'           => trim($row['CHARACTER_SET_NAME']),
                'collation'         => trim($row['COLLATION_NAME']),
                'default_value'     => trim($row['COLUMN_DEFAULT']),
                'is_auto_increment' => strpos(strtolower($row['EXTRA']), 'auto_increment') !== false,
                'is_nullable'       => strtolower(trim($row['IS_NULLABLE'])) == 'yes',
                'length'            => (int) end($length),
                'type'              => trim($row['DATA_TYPE'])
            ];
            foreach ($db->fetchRows('
                SHOW INDEX
                FROM '  . $db->t($table->getName()) . '
                WHERE ' . $db->q('Column_name') . ' LIKE ' . $db->e($this->getName()) . '
                AND '   . $db->q('Non_unique')  . ' = 0'
            ) as $index) {
                $key_name                 = trim($index['Key_name']);
                $key                      = (
                    strtolower($key_name) == 'primary'
                        ? 'primary_key'
                        : 'unique_key'
                );
                $res['is_' . $key]        = true;
                $res[$key  . '_name']     = $key_name;
            }
            return $res;
        }

        protected function getRenameQuery(string $name): string {
            $db = $this->getDb();
            return '
                ALTER TABLE ' . $db->t($this->getTable()->getName()) . '
                CHANGE '      . $db->q($this->getName()) . ' ' . $db->q($name) .
                ' ' . $this->getType() .
                (
                    !$this->getLength()
                        ? ''
                        : '(' . $db->e($this->getLength()) . ')'
                )
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
                            ALTER TABLE ' . $db->t($table->getName()) . '
                            DROP PRIMARY KEY
                        ';
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
                            DROP KEY '    . $db->q($this->getUniqueKeyName())
                        ;
            }
            $res[] = '
                ALTER TABLE ' . $db->t($table->getName()) . '
                MODIFY '      . static::getCreateQuery($db, $params)
            ;
            return $res;
        }

    }
