<?php
    /**
    * @package   project/core
    * @version   1.0.0 04.05.2018
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
            $columns_names = null,
            $db            = null,
            $engine        = '',
            $keys          = null,
            $name          = '',
            $schema        = '';

        public static function init(
            string              $name,
            \Ada\Core\Db\Driver $db,
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
            string              $name,
            \Ada\Core\Db\Driver $db
        ) {
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
            $params           = $this->extractParams();
            if (!$params) {
                throw new \Ada\Core\Exception(
                    (
                        'No table \''    . $this->getName()          . '\' ' .
                        'in database \'' . $this->getDb()->getName() . '\''
                    ),
                    1
                );
            }
            $this->setProps($params);
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

        public function getColumn(
            string $name   = '',
            bool   $cached = true
        ): \Ada\Core\Db\Column {
            $class = $this->getDb()->getNameSpace() . 'Column';
            return $class::init($name, $this, $cached);
        }

        public function getColumns(
            bool $as_objects = false,
            bool $cached     = true
        ): array {
            if (!$cached || $this->columns_names === null) {
                $this->columns_names = $this->getDb()->fetchColumn(
                    $this->getQueryColumnsNames()
                );
            }
            if (!$as_objects) {
                return $this->columns_names;
            }
            $res = [];
            foreach ($this->columns_names as $name) {
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

        public function getKeys(
            bool $grouped = true,
            bool $cached  = true
        ): array {
            if (!$cached || $this->keys === null) {
                $this->keys = $this->extractKeys();
            }
            return
                $grouped
                    ? $this->keys
                    : array_merge(...array_values($this->keys));
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

        public function updateRow(array $row, string $condition): bool {
            return $this->getDb()->updateRow($this->getName(), $row, $condition);
        }

        protected function extractKeys(): array {
            $res = [];
            $db  = $this->getDb();
            foreach ($db->fetchRows('
                SHOW INDEX
                FROM '  . $db->t($this->getName()) . '
                WHERE ' . $db->q('Non_unique')     . ' = 0
            ') as $row) {
                $key = trim($row['Key_name']);
                $res[
                    strtolower(trim($key)) == 'primary'
                        ? 'primary'
                        : 'unique'
                ][$key][] = trim($row['Column_name']);
            }
            return $res;
        }

        protected function extractParams(): array {
            $db  = $this->getDb();
            $row = $db->fetchRow('
                SELECT ' . $db->qs([
                    'CHARACTER_SET_NAME',
                    'COLLATION_NAME',
                    'ENGINE',
                    'TABLE_SCHEMA'
                ]) . '
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

        protected function getQueryColumnsNames(): string {
            $db = $this->getDb();
            return '
                SELECT ' . $db->q('COLUMN_NAME') . '
                FROM '   . $db->q('INFORMATION_SCHEMA.COLUMNS') . '
                WHERE '  . $db->q('TABLE_SCHEMA') . ' LIKE ' . $db->e($db->getName()) . '
                AND '    . $db->q('TABLE_NAME')   . ' LIKE ' . $db->e($this->getName(true, false));
        }

    }
