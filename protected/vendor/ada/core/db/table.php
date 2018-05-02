<?php
    /**
    * @package   project/core
    * @version   1.0.0 23.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Table extends \Ada\Core\Proto {

        protected static
            $instances   = [];

        protected
            $charset     = '',
            $collation   = '',
            $columns     = [],
            $db          = null,
            $engine      = '',
            $init_params = [],
            $name        = '',
            $schema      = '';

        public static function init(
            \Ada\Core\Db\Driver $db,
            string              $name   = '',
            bool                $cached = true
        ): \Ada\Core\Db\Table {
            $res =&
                static::$instances
                [$db->getDriver()]
                [$db->getName()]
                [$db->getSchema()]
                [\Ada\Core\Clean::cmd($name)]
                ?? null;
            if ($name && $res && $cached) {
                return $res;
            }
            return $res = new static(...func_get_args());
        }

        protected function __construct(
            \Ada\Core\Db\Driver $db,
            string              $name   = ''
        ) {
            $this->db          = $db;
            $name              = \Ada\Core\Clean::cmd($name);
            $dot               = strpos($name, '.');
            if ($dot === false) {
                $this->name    = $name;
                $this->schema  = $db->getSchema();
            }
            else {
                $this->name    = substr($name, $dot + 1);
                $this->schema  = substr($name, 0, $dot);
            }
            if (!$this->getName()) {
                return;
            }
            $this->init_params = $this->extractParams();
            if (!$this->init_params) {
                throw new \Ada\Core\Exception(
                    (
                        'No table \''    . $this->getName()          . '\' ' .
                        'in database \'' . $this->getDb()->getName() . '\''
                    ),
                    1
                );
            }
            $this->setProps($this->init_params);
        }

        public function delete(): bool {
            if (!$this->exists()) {
                return true;
            }
            try {
                $res = $this->getDb()->exec($this->getQueryDelete());
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    (
                        'Failed to delete table \'' . $this->init_params['name']   . '\' '  .
                        'of database \''            . $this->getDb()->getName()    . '\'. ' .
                        $e->getMessage()
                    ),
                    5
                );
            }
            if ($res) {
                $this->init_params = [];
            }
            return $res;
        }

        public function deleteRow(string $condition): bool {
            return $this->getDb()->deleteRow($this->getName(), $condition);
        }

        public function exists(): bool {
            return (bool) $this->init_params;
        }

        public function getCharset(): string {
            return $this->charset;
        }

        public function getCollation(): string {
            return $this->collation;
        }

        public function getColumn(
            string $name   = '',
            bool   $cached = true
        ): \Ada\Core\Db\Column {
            $class = $this->getDb()->getNameSpace() . 'Column';
            $res   = $class::init($this, $name, $cached);
            if ($res->getName()) {
                $this->columns[$res->getName()] = $res;
            }
            return $res;
        }

        public function getColumns(
            bool $as_objects = false,
            bool $cached     = true
        ): array {
            if (!$cached || empty($this->init_params['columns_names'])) {
                $columns_names = $this->getDb()->fetchColumn(
                    $this->getQueryColumnsNames()
                );
            }
            if (!$columns_names) {
                return [];
            }
            $this->init_params['columns_names'] = $columns_names;
            if (!$as_objects) {
                return $this->init_params['columns_names'];
            }
            $res = [];
            foreach ($this->init_params['columns_names'] as $name) {
                $res[$name] = $this->getColumn($name, $cached);
            }
            return $res;
        }

        public function getDb(): \Ada\Core\Db\Driver {
            return $this->db;
        }

        public function getEngine(): string {
            return $this->engine;
        }

        public function getName(
            bool $prefix = false,
            bool $schema = null
        ): string {
            if ($schema === true) {
                $schema_name = $this->getSchema();
            }
            elseif ($schema === false) {
                $schema_name = '';
            }
            else {
                $schema_name = (
                    $this->getDb()->getSchema() == $this->getSchema()
                        ? ''
                        : $this->getSchema()
                );
            }
            return
                (!$schema_name ? '' : $this->getSchema() . '.') .
                (!$prefix      ? '' : $this->getDb()->getPrefix()) .
                $this->name;
        }

        public function getSchema(): string {
            return $this->schema;
        }

        public function insertRow(array $row): bool {
            return $this->getDb()->insertRow($this->getName(), $row);
        }

        public function save(): bool {
            $db    = $this->getDb();
            $props = array_intersect_key($this->getProps(), $this->init_params);
            if (
                $this->exists() &&
                !\Ada\Core\Arr::init($this->init_params)->diffRecursive($props)
            ) {
                return true;
            }
            $error = '
                Failed to save table '  .
                (!$this->getName() ? '' : '\'' . $this->getName() . '\'') . '
                of database \''         . $this->getDb()->getName() . '\'
            ';
            if (!$this->getName()) {
                throw new \Ada\Core\Exception(
                    $error . '. Table name must not be empty',
                    2
                );
            }
            if (!$this->getColumns()) {
                throw new \Ada\Core\Exception(
                    $error . '. Table must contain at least one column',
                    3
                );
            }
            $method = 'getQueries' . ($this->exists() ? 'Update' : 'Create');
            $db->beginTransaction();
            try {
                foreach ($this->$method() as $query) {
                    $db->exec($query);
                }
            } catch (\Throwable $e) {
                $db->rollBackTransaction();
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    4
                );
            }
            $db->commitTransaction();
            $this->init_params = $this->extractParams();
            $this->setProps($this->init_params);
            return true;
        }

        public function setCharset(string $charset) {
            $this->charset = \Ada\Core\Clean::cmd($charset);
        }

        public function setCollation(string $collation) {
            $this->collation = \Ada\Core\Clean::cmd($collation);
        }

        public function setColumn(array $column_params) {
            $column = $this->getColumn();
            foreach ($column_params as $k => $v) {
                $setter = 'set' . \Ada\Core\Str::toCamelCase($k);
                if (method_exists($column, $setter)) {
                    $column->$setter($v);
                }
            }
            if ($column->getName()) {
                $this->columns[$column->getName()] = $column;
            }
        }

        public function setColumns(array $columns_params) {
            foreach ($columns_params as $column_params) {
                $this->setColumn($column_params);
            }
        }

        public function setEngine(string $engine) {
            $this->engine = \Ada\Core\Clean::cmd($engine);
        }

        public function setName(string $name) {
            $this->name = \Ada\Core\Clean::cmd($name);
        }

        public function setSchema(string $schema) {
            $this->schema = \Ada\Core\Clean::cmd($schema);
        }

        public function updateRow(array $row, string $condition): bool {
            return $this->getDb()->updateRow($this->getName(), $row, $condition);
        }

        protected function extractParams(): array {
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

        protected function getQueriesCreate(): array {
            $db             = $this->getDb();
            $colums_queries = [];
            $primary_keys   = [];
            $unique_keys    = [];
            foreach ($this->getColumn() as $column) {
                $colums_queries[]   = $column->getQueryCreateUpdate();
                if ($column->getPrimaryKey()) {
                    $primary_keys[] = $column->getPrimaryKey();
                }
                if ($column->getUniqueKey()) {
                    $unique_keys[]  = $column->getUniqueKey();
                }
            }
            return ['
                CREATE TABLE ' . $db->t($this->getName()) . '(
                    ' . implode(', ', $colums_queries) . (
                        !$primary_keys
                            ? ''
                            : ', PRIMARY KEY (' . $db->q(reset($primary_keys)) . ')'
                    ) . (
                        !$unique_keys
                            ? ''
                            : ', UNIQUE (' .
                                implode(', ', array_map([$db, 'q'], $unique_keys)) .
                            ')'
                    ) . '
                )
            '];
        }

        protected function getQueriesUpdate(): array {
            $res = [];
            if ($this->getName() != $this->init_params['name']) {
                $res[] = $this->getQueryRename();
            }
            if ($this->getCharset() != $this->init_params['charset']) {
                $res[] = $this->getQueryChangeCharset();
            }
            if ($this->getCollation() != $this->init_params['collation']) {
                $res[] = $this->getQueryChangeCollation();
            }
            if ($this->getEngine() != $this->init_params['engine']) {
                $res[] = $this->getQueryChangeEngine();
            }
            $res[] = $this->getQueryUpdate();
            return $res;
        }

        protected function getQueryChangeCharset(): string {
            $db = $this->getDb();
            return '
                ALTER TABLE '       . $db->t($this->getName()) . '
                DEFAULT CHARSET = ' . $db->q($this->getCharset());
        }

        protected function getQueryChangeCollation(): string {
            $db = $this->getDb();
            return '
                ALTER TABLE ' . $db->t($this->getName()) . '
                COLLATE = '   . $db->q($this->getCollation());
        }

        protected function getQueryChangeEngine(): string {
            $db = $this->getDb();
            return '
                ALTER TABLE ' . $db->t($this->getName()) . '
                ENGINE = '    . $db->q($this->getCollation());
        }

        protected function getQueryColumnsNames(): string {
            $db = $this->getDb();
            return ('
                SELECT ' . $db->q('COLUMN_NAME') . '
                FROM '   . $db->q('INFORMATION_SCHEMA.COLUMNS') . '
                WHERE '  . $db->q('TABLE_SCHEMA') . ' LIKE ' . $db->e($db->getName()) . '
                AND '    . $db->q('TABLE_NAME')   . ' LIKE ' . $db->e($this->getName(true, false))
            );
        }

        protected function getQueryDelete(): string {
            return 'DROP TABLE ' . $this->getDb()->t($this->getName());
        }

        protected function getQueryRename(string $name): string {
            $db = $this->getDb();
            return ('
                ALTER TABLE ' . $db->t($this->getName()) . '
                RENAME TO '   . $db->t($name)
            );
        }

    }
