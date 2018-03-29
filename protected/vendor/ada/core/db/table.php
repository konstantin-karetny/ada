<?php
    /**
    * @package   project/core
    * @version   1.0.0 29.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Table extends \Ada\Core\Proto {

        const
            DEFAULT_ENGINE = '';

        protected static
            $caches        = [];

        protected
            $cache         = [],
            $charset       = '',
            $collation     = '',
            $columns       = [],
            $db            = null,
            $engine        = '',
            $exists        = false,
            $name          = '',
            $schema        = '';

        public static function init(string $name, $db, bool $cached = true) {
            return new static(...func_get_args());
        }

        protected function __construct(
            string $name,
            Driver $db,
            bool   $cached = true
        ) {
            $this->db      = $db;
            $this->charset = $db->getCharset();
            $this->name    = \Ada\Core\Clean::cmd($name);
            $this->cache   =& $this->getCache();
            if (!$cached) {
                $this->cache = [];
            }
            $this->exists  = $this->load();
        }

        abstract public function create();

        public function delete(): bool {
            $db = $this->getDb();
            return $db->exec('DROP TABLE ' . $db->t($this->getName()));
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

        public function getColumn(string $name): Column {
            $columns = $this->getColumns();
            if (!isset($columns[$name])) {
                $class = $this->getDb()->getNameSpace() . 'Column';
                return $class::init($name, $this);
            }
            return $columns[$name];
        }

        public function getColumns(): array {
            $this->loadColumns();
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
            $db = $this->getDb();
            return $db->exec('
                RENAME TABLE ' . $db->t($this->name) . '
                TO           ' . $db->t(\Ada\Core\Clean::cmd($name))
            );
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

        protected function &getCache(): array {
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

        abstract protected function load(): bool;

        abstract protected function loadColumns(): bool;

    }
