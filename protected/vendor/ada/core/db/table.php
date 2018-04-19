<?php
    /**
    * @package   project/core
    * @version   1.0.0 19.04.2018
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
            if (empty($params['columns'])) {
                throw new \Ada\Core\Exception(
                    $error . '. Table name must not be empty',
                    2
                );
            }
            $params = static::preapreParams($params);
            $error  = (
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
                $db->exec(static::getCreateQuery($db, $params));
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    3
                );
            }
            return $db->getTable($params['name']);
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
            $this->db         = $db;
            $name             = \Ada\Core\Clean::cmd($name);
            $dot              = strpos($name, '.');
            if ($dot === false) {
                $this->name   = $name;
                $this->schema = $db->getSchema();
            }
            else {
                $this->name   = substr($name, $dot + 1);
                $this->schema = substr($name, 0, $dot);
            }
            $props            = $this->extractProps();
            if (!$props) {
                throw new \Ada\Core\Exception(
                    (
                        'No table \''     . $this->getName()          . '\' ' .
                        'in database \'' . $this->getDb()->getName() . '\''
                    ),
                    4
                );
            }
            $this->setProps($props);
        }

        public function createColumn(array $params): Column {
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
                    7
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

        public function getName(
            bool $prefix = false,
            bool $schema = true
        ): string {
            return (
                ($schema && $this->getSchema() ? ($this->getSchema() . '.') : '') .
                ($prefix ? $this->getDb()->getPrefix() : '') .
                $this->name
            );
        }

        public function getSchema(): string {
            return $this->schema;
        }

        public function insertRow(array $row): bool {
            return $this->getDb()->insertRow($this->getName(), $row);
        }

        public function rename(string $name): bool {
            $name  = \Ada\Core\Clean::cmd($name);
            $error = 'Failed to rename table \'' . $this->getName() . '\'';
            if (!$name) {
                throw new \Ada\Core\Exception(
                    $error . '. Table name must not be empty',
                    5
                );
            }
            try {
                if (!$this->getDb()->exec($this->getRenameQuery($name))) {
                    return false;
                }
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    $error . ' \' to \'' . $name . '\'. ' . $e->getMessage(),
                    6
                );
            }
            $this->name = $name;
            return true;
        }

        public function updateRow(array $row, string $condition): bool {
            return $this->getDb()->updateRow($this->getName(), $row, $condition);
        }

        protected static function getCreateQuery($db, array $params): string {
            $columns   = '';
            $primaries = [];
            $uniques   = [];
            $class     = $db->getNameSpace() . 'Column';
            foreach ($params['columns'] as $column_params) {
                $column_params   = $class::preapreParams($column_params);
                $columns        .= (
                    $class::getCreateQuery($db, $column_params) . ', '
                );
                if ($column_params['primary_key']) {
                    $primaries[] = $column_params['primary_key'];
                }
                if ($column_params['unique_key']) {
                    $uniques[]   = $column_params['unique_key'];
                }
            }
            $columns = rtrim($columns, ', ');
            if ($primaries) {
                $columns .= ', PRIMARY KEY (' . $db->q(reset($primaries)) . ')';
            }
            if ($uniques) {
                $columns .= ', UNIQUE (';
                foreach ($uniques as $unique) {
                    $columns .= $db->q($unique) . ', ';
                }
                $columns = rtrim($columns, ', ') . ')';
            }
            return '
                CREATE TABLE ' . $db->t($params['name']) . '
                (' . $columns . ')
            ';
        }

        protected static function preapreParams(array $params): array {
            foreach (get_class_vars(__CLASS__) as $k => $v) {
                $params[$k] = $params[$k] ?? $v;
                switch ($k) {
                    case 'charset'   :
                    case 'collation' :
                    case 'engine'    :
                    case 'name'      :
                    case 'schema'    :
                        $params[$k] = \Ada\Core\Clean::cmd($params[$k]);
                        break;
                    case 'columns'   :
                        $params[$k] = (array) $params[$k];
                        break;
                    default          :
                        unset($params[$k]);
                }
            }
            return $params;
        }

        protected function extractProps(): array {
            $db  = $this->getDb();
            $row = $db->fetchRow('
                SELECT *
                FROM '  . $db->q('information_schema.TABLES', 't') . '
                JOIN '  . $db->q('information_schema.COLLATION_CHARACTER_SET_APPLICABILITY', 'ccsa') . '
                ON '    . $db->q('ccsa.COLLATION_NAME') . ' = '    . $db->q('t.TABLE_COLLATION') . '
                WHERE ' . $db->q('t.TABLE_SCHEMA')      . ' LIKE ' . $db->e($db->getName()) . '
                AND '   . $db->q('t.TABLE_NAME')        . ' LIKE ' . $db->e($this->getName(true, false)) . '
            ');
            return
                $row
                    ? [
                        'charset'   => trim($row['CHARACTER_SET_NAME']),
                        'collation' => trim($row['COLLATION_NAME']),
                        'engine'    => trim($row['ENGINE']),
                        'schema'    => trim($row['TABLE_SCHEMA'])
                    ]
                    : [];
        }

        protected function getColumnsNamesQuery(): string {
            $db = $this->getDb();
            return ('
                SELECT ' . $db->q('COLUMN_NAME') . '
                FROM '   . $db->q('INFORMATION_SCHEMA.COLUMNS') . '
                WHERE '  . $db->q('TABLE_SCHEMA') . ' LIKE ' . $db->e($db->getName()) . '
                AND '    . $db->q('TABLE_NAME')   . ' LIKE ' . $db->e($this->getName(true, false))
            );
        }

        protected function getDeleteQuery(): string {
            return 'DROP TABLE ' . $this->getDb()->t($this->getName());
        }

        protected function getRenameQuery(string $name): string {
            $db = $this->getDb();
            return ('
                ALTER TABLE ' . $db->t($this->getName()) . '
                RENAME TO '   . $db->t($name)
            );
        }

    }
