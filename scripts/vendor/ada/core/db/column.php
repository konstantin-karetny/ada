<?php
    /**
    * @package   ada/core
    * @version   1.0.0 16.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    class Column extends \Ada\Core\Proto {

        protected
            $is_auto_increment = false,
            $collation         = '',
            $default_value     = null,
            $length            = '',
            $name              = '',
            $is_null           = false,
            $is_primary_key    = false,
            $table             = false,
            $type              = 'int';

        public function __construct(\Ada\Core\Db\Table $table, string $name) {
            $this->table = $table;
            $this->setName($name);
        }

        public function create(self $after = null): bool {
            $db = $this->getDb();
            return $db->exec('
                ALTER TABLE ' . $db->t($this->getTable()->getName()) . '
                ADD '         . $db->q($this->getName()) . ' ' .
                $this->getType() .
                (
                    $this->getLength()
                        ? ('(' . $db->esc($this->getLength()) . ')')
                        : ''
                ) .
                (
                    $this->getCollation()
                        ? (' COLLATE ' . $db->esc($this->getCollation()))
                        : ''
                ) .
                (
                    ($this->isNull() ? '' : ' NOT') . ' NULL'
                ) .
                (
                    $this->getDefaultValue()
                        ? (' DEFAULT' . $db->esc($this->getDefaultValue()))
                        : ''
                ) .
                (
                    $after ? (' AFTER ' . $db->q($after->getName())) : ''
                )
            );
        }

        public function delete(): bool {

        }

        public function update(): bool {

        }

        public function isAutoIncrement(): bool {
            return $this->is_auto_increment;
        }

        public function getCollation(): string {
            return $this->collation;
        }

        public function getDb(): Drivers\Driver {
            return $this->getTable()->getDb();
        }

        public function getDefaultValue() {
            return $this->default_value;
        }

        public function getLength() {
            return $this->length;
        }

        public function getName(): string {
            return $this->name;
        }

        public function isNull(): bool {
            return $this->is_null;
        }

        public function isPrimaryKey(): bool {
            return $this->is_primary_key;
        }

        public function getTable(): \Ada\Core\Db\Table {
            return $this->table;
        }

        public function getType(): string {
            return $this->type;
        }

        public function setIsAutoIncrement(bool $is_auto_increment) {
            $this->is_auto_increment = $is_auto_increment;
        }

        public function setCollation(string $collation) {
            $this->collation = \Ada\Core\Clean::cmd($collation);
        }

        public function setDefaultValue($default_value) {
            $this->default_value = $default_value;
        }

        public function setLength(string $length) {
            $this->length = \Ada\Core\Clean::value($length);
        }

        public function setName(string $name) {
            $this->name = \Ada\Core\Clean::cmd($name);
        }

        public function setIsNull(bool $is_null) {
            $this->is_null = $is_null;
        }

        public function setIsPrimaryKey(bool $is_primary_key) {
            $this->is_primary_key = $is_primary_key;
        }

        public function setType(string $type) {
            $this->type = \Ada\Core\Clean::cmd($type);
        }

    }
