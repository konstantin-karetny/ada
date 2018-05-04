<?php
    /**
    * @package   project/core
    * @version   1.0.0 04.05.2018
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
            $name              = '',
            $primary_key       = '',
            $table             = null,
            $type              = '',
            $unique_key        = '';

        public static function init(
            string             $name,
            \Ada\Core\Db\Table $table,
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
            string             $name,
            \Ada\Core\Db\Table $table
        ) {
            $this->name  = \Ada\Core\Clean::cmd($name);
            $this->table = $table;
            $params      = $this->extractParams();
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

        public function getTable(): \Ada\Core\Db\Table {
            return $this->table;
        }

        public function getType(): string {
            return $this->type;
        }

        public function getUniqueKey(): string {
            return $this->unique_key;
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
                'default_value'     => \Ada\Core\Type::set(trim($row['COLUMN_DEFAULT'])),
                'is_auto_increment' => strpos(strtolower($row['EXTRA']), 'auto_increment') !== false,
                'is_nullable'       => strtolower(trim($row['IS_NULLABLE'])) == 'yes',
                'name'              => trim($row['COLUMN_NAME']),
                'primary_key'       => '',
                'type'              => strtolower(trim($row['DATA_TYPE'])),
                'type_args'         => \Ada\Core\Clean::values(explode(',', end($type_args)), 'int'),
                'unique_key'        => ''
            ];
            foreach ($table->getKeys() as $group => $keys) {
                foreach ($keys as $key => $names) {
                    if (in_array($this->getName(), $names)) {
                        $res[$group . '_key'] = $key;
                        break 1;
                    }
                }
            }
            return $res;
        }

    }
