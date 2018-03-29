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
            $cache         = [];

        protected
            $charset       = '',
            $collation     = '',
            $columns       = [],
            $db            = null,
            $engine        = '',
            $exists        = false,
            $name          = '';

        public static function init(string $name, $db, bool $cached) {
            return new static(...func_get_args());
        }

        protected function __construct(string $name, Driver $db, bool $cached) {
            $this->db      = $db;
            $this->charset = $db->getCharset();
            $this->name    = \Ada\Core\Clean::cmd($name);
            $this->exists  = $this->load($cached);
        }

        abstract public function create();

        public function delete(): bool {
            $db = $this->getDb();
            return $db->exec('DROP TABLE ' . $db->t($this->getName()));
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
            $this->loadColumns($cached);
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
            foreach (get_class_methods($column) as $method) {
                if (substr($method, 0, 3) != 'get') {
                    continue;
                }
                $setter = 's' . substr($method, 1);
                if (method_exists($res, $setter)) {
                    $res->$setter($column->$method());
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

        protected function cache(string $name, $value = null) {
            $db      = $this->getDb();
            $driver  = $db->getDriver();
            $db_name = $db->getName();
            $t_name  = $this->getName(true);
            if (!isset(static::$cache[$driver])) {
                static::$cache[$driver] = [];
            }
            if (!isset(static::$cache[$driver][$db_name])) {
                static::$cache[$driver][$db_name] = [];
            }
            if (!isset(static::$cache[$driver][$db_name][$t_name])) {
                static::$cache[$driver][$db_name][$t_name] = [];
            }
            if ($value === null) {
                return static::$cache[$driver][$db_name][$t_name][$name] ?? null;
            }
            static::$cache[$driver][$db_name][$t_name][$name] = $value;
        }

        abstract protected function load(bool $cached = true): bool;

        abstract protected function loadColumns(bool $cached = true): bool;

    }
