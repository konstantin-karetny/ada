<?php
    /**
    * @package   project/core
    * @version   1.0.0 17.04.2018
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
            $table             = null,
            $type              = 'int';

        public static function create($table, array $params): self {
            $params = static::preapreParams($params);
            $error  = (
                'Failed to add column ' .
                (!$params['name'] ? '' : '\'' . $params['name'] . '\'') .
                ' to table \'' . $table->getName() . '\''
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
                        'No column \''    . $this->getName()          . '\'' .
                        ' in table \''    . $table->getName()         . '\'' .
                        ' of database \'' . $this->getDb()->getName() . '\''
                    ),
                    3
                );
            }
            $this->setProps($props);
        }

        public function change(array $params): bool {
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
            try {
                foreach ($this->getChangeQueries($params) as $query) {
                    $db->exec($query);
                }
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    (
                        'Failed to change column \'' . $this->getName(). '\'' .
                        ' of table \'' . $table->getName() . '\'' .
                        '. ' . $e->getMessage()
                    ),
                    4
                );
            }
            $table->getColumn($params['name'], false);
            return true;
        }

        public function delete(): bool {

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

        public function getTable(): Table {
            return $this->table;
        }

        public function getType(): string {
            return $this->type;
        }

        protected static function getCreateQueries(
                  $table,
            array $params
        ): array {
            $res   = [];
            $db    = $this->getDb();
            $res[] = ('
                ALTER TABLE ' . $db->t($table->getName()) . '
                ADD '         . static::getCreateQuery($db, $params)
            );
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

        protected function getChangeQueries(array $params): array {
            $res   = [];
            $db    = $this->getDb();
            $table = $this->getTable();
            if ($params['name'] != $this->getName()) {
                $res[] = $this->getRenameQuery($params['name']);
            }
            $res[] = '
                ALTER TABLE ' . $db->t($table->getName()) . '
                MODIFY '      . static::getCreateQuery($db, $params)
            ;
            if ($params['is_primary_key'] != $this->getIsPrimaryKey()) {
                $res[] =
                    $params['is_primary_key']
                        ? '
                            ALTER TABLE '      . $db->t($table->getName()) . '
                            ADD PRIMARY KEY (' . $db->q($this->getName()) . ')
                        '
                        : '
                            ALTER TABLE ' . $db->t($table->getName()) . '
                            DROP PRIMARY KEY
                        ';
            }
            return $res;
        }

        protected function getProps(): array {
            $table = $this->getTable();
            $db    = $this->getDb();
            $row   = $db->fetchRow('
                SELECT *
                FROM '  . $db->q('information_schema.COLUMNS') . '
                WHERE ' . $db->q('TABLE_SCHEMA') . ' LIKE ' . $db->e($table->getSchema()) . '
                AND '   . $db->q('TABLE_NAME')   . ' LIKE ' . $db->e($table->getName(true, false)) . '
                AND '   . $db->q('COLUMN_NAME')  . ' LIKE ' . $db->e($this->getName())
            );
            if (!$row) {
                return [];
            }
            $key         = strtolower(trim($row['COLUMN_KEY']));
            $type_length = explode('(', rtrim($row['COLUMN_TYPE'], ')'));
            $res         = [
                'charset'           => trim($row['CHARACTER_SET_NAME']),
                'collation'         => trim($row['COLLATION_NAME']),
                'default_value'     => trim($row['COLUMN_DEFAULT']),
                'is_auto_increment' => (
                    stripos('auto_increment', strtolower($row['EXTRA'])) !== false
                ),
                'is_nullable'       => strtolower(trim($row['IS_NULLABLE'])) == 'yes',
                'is_primary_key'    => $key                                  == 'pri',
                'is_unique_key'     => $key                                  == 'uni',
                'length'            => (int) ($type_length[1] ?? 0),
                'type'              => trim($type_length[0])
            ];
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

    }
