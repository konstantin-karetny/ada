<?php
    /**
    * @package   project/core
    * @version   1.0.0 30.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Table extends \Ada\Core\Proto {

        protected static
            $caches    = [];

        protected
            $cache     = [],
            $charset   = '',
            $collation = '',
            $columns   = [],
            $db        = null,
            $engine    = '',
            $exists    = false,
            $name      = '',
            $schema    = '';

        public static function init(string $name, $db, bool $cached = true) {
            return new static(...func_get_args());
        }

        protected function __construct(
            string $name,
            Driver $db,
            bool   $cached = true
        ) {
            $this->db    = $db;
            $this->name  = \Ada\Core\Clean::cmd($name);
            $this->cache =& $this->getLinkToCache();
            $this->setProps($this->fetchDbData($cached));
        }

        abstract public function create(): bool;

        public function delete(): bool {
            $db = $this->getDb();
            if (
                !$db->exec('DROP TABLE ' . $db->t($this->getName()))
            ) {
                return false;
            }
            $this->reInit();
            return true;
        }

        public function deleteRow(string $condition): bool {
            return $this->getDb()->deleteRow($this->getName(), $condition);
        }

        public function exists(): int {
            return $this->exists;
        }

        public function getCharset(): string {
            return $this->charset;
        }

        public function getCollation(): string {
            return $this->collation;
        }

        public function getColumn(string $name, bool $cached = true): Column {
            $columns = $this->getColumns($cached);
            if (!isset($columns[$name])) {
                $class = $this->getDb()->getNameSpace() . 'Column';
                return $class::init($name, $this);
            }
            return $columns[$name];
        }

        public function getColumns(bool $cached = true): array {
            return array_merge($this->fetchColumns($cached), $this->columns);
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
            $db   = $this->getDb();
            $name = \Ada\Core\Clean::cmd($name);
            if (
                !$db->exec('
                    RENAME TABLE ' . $db->t($this->name) . '
                    TO           ' . $db->t($name)
                )
            ) {
                return false;
            }
            $this->name = $name;
            return true;
        }

        public function setCharset(string $charset) {
            $this->charset = \Ada\Core\Clean::cmd($charset);
        }

        public function setCollation(string $collation) {
            $this->collation = \Ada\Core\Clean::cmd($collation);
        }

        public function setColumn(Column $column) {
            $class = $this->getDb()->getNameSpace() . 'Column';
            $res   = $class::init($column->getName(), $this);
            foreach (get_class_methods($column) as $getter) {
                if (substr($getter, 0, 3) != 'get') {
                    continue;
                }
                $setter = 's' . substr($getter, 1);
                if (method_exists($res, $setter)) {
                    $res->$setter($column->$getter());
                }
            }
            $this->columns[$res->getName()] = $res;
        }

        public function setColumns(array $columns) {
            foreach ($columns as $column) {
                $this->setColumn($column);
            }
        }

        public function setEngine(string $engine) {
            $this->engine = \Ada\Core\Clean::cmd($engine);
        }

        public function updateRow(array $row, string $condition): bool {
            return $this->getDb()->updateRow($this->getName(), $row, $condition);
        }

        abstract protected function fetchColumns(bool $cached = true): array;

        abstract protected function fetchDbData(bool $cached = true): array;

        protected function &getLinkToCache(): array {
            $db  =  $this->getDb();
            $res =& static::$caches;
            foreach ([
                $db->getDriver(),
                $db->getName(),
                $db->getSchema(),
                $this->getName(true)
            ] as $key) {
                $res[$key] =  $res[$key] ?? [];
                $res       =& $res[$key];
            }
            return $res;
        }

        protected function reInit(): bool {
            $this->cache   = [];
            $this->columns = [];
            $this->setProps($this->fetchDbData());
            return true;
        }

    }
