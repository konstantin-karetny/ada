<?php
    /**
    * @package   project/core
    * @version   1.0.0 29.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Column extends \Ada\Core\Proto {

        protected
            $collation         = '',
            $default_value     = null,
            $is_auto_increment = false,
            $is_null           = false,
            $is_primary_key    = false,
            $is_unique_key     = false,
            $length            = '',
            $name              = '',
            $table             = null,
            $type              = 'int';

        public static function init(string $name, Table $table) {
            return new static($name, $table);
        }

        public function __construct(string $name, Table $table) {
            $this->name  = \Ada\Core\Clean::cmd($name);
            $this->table = $table;
        }

        public function create(self $after = null): bool {
            $db = $this->getDb();
            return $db->exec('
                ALTER TABLE ' . $db->t($this->getTable()->getName()) . '
                ADD '         . $db->q($this->getName()) . ' ' .
                $this->getType() .
                (
                    $this->getLength()
                        ? ('(' . $db->e($this->getLength()) . ')')
                        : ''
                ) . '
                COLLATE ' . $db->e($this->getCollation()) .
                (
                    ($this->getIsNull() ? '' : ' NOT') . ' NULL'
                ) .
                (
                    $this->getDefaultValue()
                        ? (' DEFAULT' . $db->e($this->getDefaultValue()))
                        : ''
                ) .
                (
                    $after ? (' AFTER ' . $db->q($after->getName())) : ''
                )
            );
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

        public function getIsNull(): bool {
            return $this->is_null;
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

        public function setCollation(string $collation) {
            $this->collation = \Ada\Core\Clean::cmd($collation);
        }

        public function setDefaultValue($default_value) {
            $this->default_value = \Ada\Core\Type::set($default_value);
        }

        public function setIsAutoIncrement(bool $is_auto_increment) {
            $this->is_auto_increment = $is_auto_increment;
        }

        public function setIsNull(bool $is_null) {
            $this->is_null = $is_null;
        }

        public function setIsPrimaryKey(bool $is_primary_key) {
            $this->is_primary_key = $is_primary_key;
        }

        public function setIsUniqueKey(bool $is_unique_key) {
            $this->is_unique_key = $is_unique_key;
        }

        public function setLength(string $length) {
            $this->length = \Ada\Core\Type::set($length);
        }

        public function setType(string $type) {
            $this->type = trim(preg_replace('/[^ a-z0-9_\.-]/i', '', $type));
        }

    }
