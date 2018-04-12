<?php
    /**
    * @package   project/core
    * @version   1.0.0 13.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Table extends \Ada\Core\Proto {

        protected static
            $instances     = [];

        protected
            $charset       = '',
            $collation     = '',
            $columns       = [],
            $columns_names = [],
            $db            = null,
            $engine        = '',
            $name          = '',
            $schema        = '';

        public static function create(Driver $db, array $params): self {
            $defaults = get_class_vars(__CLASS__);
            foreach ([
                'charset',
                'collation',
                'columns',
                'engine',
                'name',
                'schema'
            ] as $prop) {
                $params[$prop] = $params[$prop] ?? $defaults[$prop];
                switch ($prop) {
                    case 'columns':
                        $params[$prop] = (array) $params[$prop];
                    default:
                        $params[$prop] = \Ada\Core\Clean::cmd($params[$prop]);
                }
            }
            $error = (
                'Failed to create table' .
                (!$params['name'] ? '' : ' \'' . $params['name'] . '\'')
            );
            if (!$params['name']) {
                throw new \Ada\Core\Exception(
                    $error . '. Table name must not be empty',
                    1
                );
            }
            try {
                $db->exec($this->getCreateQuery($params));
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    2
                );
            }
            $table = $db->getTable($params['name']);
            foreach ($params['columns'] as $column) {
                $table->addColumn($column);
            }
            return $table;
        }

        public static function init(
            string $name,
                   $db,
            bool   $cached = true
        ): self {
            $res =&
                static::$instances
                [$db->getDriver()]
                [$db->getName()]
                [$db->getSchema()]
                [\Ada\Core\Clean::cmd($name)]
                ?? null;
            if ($cached && $res) {
                return $res;
            }
            return $res = new static(...func_get_args());
        }

        protected function __construct(string $name, Driver $db) {
            $this->db   = $db;
            $this->name = \Ada\Core\Clean::cmd($name);
            $props      = $this->getProps();
            if (!$props) {
                throw new \Ada\Core\Exception(
                    (
                        'No table \''     . $this->getName()          . '\'' .
                        ' in database \'' . $this->getDb()->getName() . '\''
                    ),
                    3
                );
            }
            $this->setProps($props);
        }

        public function addColumn(array $params): Column {
            $class = $this->getDb()->getNameSpace() . 'Column';
            return $class::create($this, $params);
        }

        public function delete(): bool {
            $db = $this->getDb();
            try {
                if (!$db->exec($this->getDeleteQuery())) {
                    return false;
                }
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    (
                        'Failed to delete table \'' . $this->getName() . '\'. ' .
                        $e->getMessage()
                    ),
                    6
                );
            }
            unset(
                static::$instances
                [$db->getDriver()]
                [$db->getName()]
                [$db->getSchema()]
                [$this->getName()]
            );
            $this->drop();
            return true;
        }

        public function deleteRow(string $condition): bool {
            return $this->getDb()->deleteRow($this->getName(), $condition);
        }

        public function getCharset(): string {
            return $this->charset;
        }

        public function getCollation(): string {
            return $this->collation;
        }

        public function getColumn(string $name, bool $cached = true): Column {
            $class = $this->getDb()->getNameSpace() . 'Column';
            $res   = $class::init($name, $this, $cached);
            return $this->columns[$res->getName()] = $res;
        }

        public function getColumns(
            bool $as_objects = false,
            bool $cached     = true
        ): array {
            if (!$cached || !$this->columns_names) {
                $this->columns_names = $this->getDb()->fetchColumn(
                    $this->getColumnsNamesQuery()
                );
            }
            if (!$as_objects) {
                return $this->columns_names;
            }
            $class = $this->getDb()->getNameSpace() . 'Column';
            foreach ($this->columns_names as $name) {
                $this->columns[$name] = $class::init($name, $this, $cached);
            }
            return $this->columns;
        }

        public function getDb(): Driver {
            return $this->db;
        }

        public function getEngine(): string {
            return $this->engine;
        }

        public function getName(bool $prefix = false): string {
            return ($prefix ? $this->getDb()->getPrefix() : '') . $this->name;
        }

        public function getSchema(): string {
            return $this->schema;
        }

        public function insertRow(array $row): bool {
            return $this->getDb()->insertRow($this->getName(), $row);
        }

        public function rename(string $name): bool {
            $db    = $this->getDb();
            $name  = \Ada\Core\Clean::cmd($name);
            $error = 'Failed to rename table \'' . $this->getName() . '\'';
            if (!$name) {
                throw new \Ada\Core\Exception(
                    $error . '. Table name must not be empty',
                    4
                );
            }
            try {
                if (
                    !$db->exec($this->getRenameQuery($name))
                ) {
                    return false;
                }
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    $error . ' \' to \'' . $name . '\'. ' . $e->getMessage(),
                    5
                );
            }
            $this->name = $name;
            return true;
        }

        public function updateRow(array $row, string $condition): bool {
            return $this->getDb()->updateRow($this->getName(), $row, $condition);
        }

        abstract protected function getColumnsNamesQuery(): string;

        protected function getCreateQuery(array $params): string {
            $db = $this->getDb();
            return (
                'CREATE TABLE ' . $db->t($params['name']) . ' ()' .
                (!$params['engine']    ? '' : ' ENGINE = '          . $db->e($params['engine'])) .
                (!$params['charset']   ? '' : ' DEFAULT CHARSET = ' . $db->e($params['charset'])) .
                (!$params['collation'] ? '' : ' COLLATE = '         . $db->e($params['collation']))
            );
        }

        protected function getDeleteQuery(): string {
            return 'DROP TABLE ' . $this->getDb()->t($this->getName());
        }

        protected function getRenameQuery(string $name): string {
            $db = $this->getDb();
            return ('
                RENAME TABLE ' . $db->t($this->getName()) . '
                TO           ' . $db->t($name)
            );
        }

        abstract protected function getProps(): array;

    }
