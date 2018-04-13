<?php
    /**
    * @package   project/core
    * @version   1.0.0 13.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Column extends \Ada\Core\Proto {

        protected static
            $instances         = [];

        protected
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
            foreach (get_class_vars(__CLASS__) as $k => $v) {
                $params[$k] = $params[$k] ?? $v;
                switch ($k) {
                    case 'collation'         :
                    case 'name'              :
                        $params[$k] = \Ada\Core\Clean::cmd($params[$k]);
                        break;
                    case 'default_value'     :
                        $params[$k] = \Ada\Core\Type::set(trim($params[$k]));
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
            $error = (
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
                        'No column \''    . $this->getName()           . '\'' .
                        ' in table \''    . $table->getName()          . '\'' .
                        ' of database \'' . $table->getDb()->getName() . '\''
                    ),
                    3
                );
            }
            $this->setProps($props);
        }

        public function delete(): bool {

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

        protected static function getCreateQueries($table, array $params): array {
            $res   = [];
            $db    = $table->getDb();
            $res[] = '
                ALTER TABLE ' . $db->t($table->getName()) . '
                ADD '         . $db->q($params['name']) . ' ' .
                $params['type'] .
                (
                    !$params['length']
                        ? ''
                        : '(' . $db->e($params['length']) . ')'
                ) .
                (
                    $params['default_value'] === ''
                        ? ''
                        : ' DEFAULT ' . $params['default_value']
                ) .
                (
                    !$params['collation']
                        ? ''
                        : ' COLLATE ' . $db->e($params['collation'])
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
                );
            if ($params['is_primary_key']) {
                $res[] = $db->exec('
                    ALTER TABLE '      . $db->t($table->getName()) . '
                    ADD PRIMARY KEY (' . $db->q($params['name']) . ')
                ');
            }
            if ($params['is_unique_key']) {
                $res[] = $db->exec('
                    ALTER TABLE ' . $db->t($table->getName()) . '
                    ADD UNIQUE (' . $db->q($params['name']) . ')
                ');
            }
            return $res;
        }

        protected function getProps(): array {
            $table = $this->getTable();
            $db    = $table->getDb();
            $row   = $db->fetchRow('
                SHOW FULL COLUMNS
                FROM ' . $db->t($table->getName()) . '
                LIKE ' . $db->e($this->getName())
            );
            if (!$row) {
                return [];
            }
            $key         = strtolower(trim($row['Key']));
            $type_length = explode('(', rtrim($row['Type'], ')'));
            $res         = [
                'collation'         => trim($row['Collation']),
                'default_value'     => $row['Default'],
                'is_auto_increment' => (
                    stripos('auto_increment', strtolower($row['Extra'])) !== false
                ),
                'is_nullable'       => strtolower(trim($row['Null'])) == 'yes',
                'is_primary_key'    => $key                           == 'pri',
                'is_unique_key'     => $key                           == 'uni',
                'length'            => (int) ($type_length[1] ?? 0),
                'type'              => trim($type_length[0])
            ];
            return $res;

        }

    }
