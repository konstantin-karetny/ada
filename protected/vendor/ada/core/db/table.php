<?php
    /**
    * @package   project/core
    * @version   1.0.0 28.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Table extends \Ada\Core\Proto {

        protected static
            $cache   = [];

        protected
            $charset = '',
            $columns = [],
            $db      = null,
            $exists  = false,
            $name    = '';

        public static function init(string $name, $db, bool $cached) {
            return new static(...func_get_args());
        }

        protected function __construct(string $name, Driver $db, bool $cached) {
            $this->db      = $db;
            $this->charset = $db->getCharset();
            $this->name    = \Ada\Core\Clean::cmd($name);
            $this->exists  = $this->load($cached);
        }

        public function create(): bool {
            if ($this->exists()) {
                return false;
            }
            $db          = $this->getDb();
            $columns     = '';
            $primary_key = '';
            $uniques     = [];
            foreach ($this->getColumns() as $column) {
                $columns .= '
                    ' .
                    $db->q($column->getName()) . ' ' .
                    $column->getType() .
                    (
                        $column->getLength()
                            ? ('(' . $db->esc($column->getLength()) . ')')
                            : ''
                    ) . '
                    COLLATE ' . $db->esc($column->getCollation()) .
                    (
                        ($column->isNull() ? '' : ' NOT') . ' NULL'
                    ) .
                    (
                        $column->getDefaultValue()
                            ? (' DEFAULT' . $db->esc($column->getDefaultValue()))
                            : ''
                    ) . ',
                ';
                if ($column->isPrimaryKey) {
                    $primary_key = $column->getName();
                }
            }
            if ($primary_key) {
                $columns .= '
                    PRIMARY KEY (' . $db->q($primary_key) . ')
                ';
            }

            exit(var_dump('
                CREATE TABLE ' . $db->t($this->getName()) . ' (
                    PRIMARY KEY  (`category_id`),
                    KEY `sort_add_date` (`category_add_date`)
                )
                ENGINE          = ' . $db->esc($this->getEngine()) . '
                DEFAULT CHARSET = ' . $db->esc($this->getCharset()) . '
                COLLATE         = ' . $db->esc($this->getCollation()) . '
            '));

        }

        public function exists(): int {
            return $this->exists;
        }

        public function getCharset(): string {
            return $this->charset;
        }

        public function getColumn(string $name, bool $cached = true): Column {
            $columns = $this->getColumns($cached);
            if (!isset($columns[$name])) {
                throw new \Ada\Core\Exception(
                    'Uncknown column \'' . $name . '\' ' .
                    'in table \'' . $this->getName() . '\'',
                    1
                );
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

        public function getName(bool $prefix = false): string {
            return ($prefix ? $this->getDb()->getPrefix() : '') . $this->name;
        }

        public function setCharset(string $charset) {
            $this->charset = \Ada\Core\Clean::cmd($charset);
        }

        public function setColumn(Column $column) {
            $this->columns[$column->getName()] = $column;
        }

        public function setColumns(array $columns) {
            foreach ($columns as $column) {
                $this->setColumn($column);
            }
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
