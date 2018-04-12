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

        abstract public static function create($table, array $params);

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

        abstract protected function getProps(): array;

    }
