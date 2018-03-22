<?php
    /**
    * @package   project/core
    * @version   1.0.0 22.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Table extends \Ada\Core\Proto {

        protected static
            $insts          = [];

        protected
            $auto_increment  = 0,
            $cache           = [],
            $charset         = '',
            $collation       = '',
            $columns         = [],
            $create_datetime = '',
            $db              = null,
            $engine          = 'InnoDB',
            $exists          = false,
            $name            = '',
            $rows_qty        = 0,
            $size            = 0;

        public static function init(string $name, $db) {
            $id = $db->getPrefix() . $name;
            return
                static::$insts[$id]
                    ?? static::$insts[$id] = new static(...func_get_args());
        }

        protected function __construct(string $name, Driver $db) {
            $this->db        = $db;
            $this->charset   = $db->getCharset();
            $this->collation = $db->getCollation();
            $this->name      = \Ada\Core\Clean::cmd($name);
            $this->exists    = $this->load();
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

        public function getAutoIncrement(): int {
            return $this->auto_increment;
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
                throw new \Ada\Core\Exception(
                    'Uncknown column \'' . $name . '\' ' .
                    'in table \'' . $this->getName() . '\'',
                    1
                );
            }
            return $columns[$name];
        }

        public function getColumns(): array {
            if (isset($this->cache['columns'])) {
                return $this->columns;
            }
            $this->cache['columns'] = true;
            return $this->columns = $this->loadColumns();
        }

        public function getCreateDatetime(): string {
            return $this->create_datetime;
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

        public function getRowsQty(): int {
            return $this->rows_qty;
        }

        public function getSize(): int {
            return $this->size;
        }

        public function setAutoIncrement(int $auto_increment) {
            $this->auto_increment = $auto_increment;
        }

        public function setCharset(string $charset) {
            $this->charset = \Ada\Core\Clean::cmd($charset);
        }

        public function setCollation(string $collation) {
            $this->collation = \Ada\Core\Clean::cmd($collation);
        }

        public function setColumn(Column $column) {
            $this->columns[$column->getName()] = $column;
        }

        public function setColumns(array $columns) {
            foreach ($columns as $column) {
                $this->setColumn($column);
            }
        }

        public function setEngine(string $engine) {
            $this->engine = \Ada\Core\Clean::cmd($engine);
        }

        protected function load(): bool {
            if (isset($this->cache['load'])) {
                return $this->cache['load'];
            }
            $db   = $this->getDb();
            $info = (array) $db->fetchRow(
                'SHOW TABLE STATUS LIKE ' . $db->esc($this->getName(true))
            );
            if (!$info) {
                return $this->cache['load'] = false;
            }
            foreach ($info as $k => $v) {
                switch ($k) {
                    case 'Auto_increment':
                        $this->setAutoIncrement($v);
                        break;
                    case 'Collation':
                        $this->setCollation($v);
                        break;
                    case 'Create_time':
                        $this->create_datetime = (string) $v;
                        break;
                    case 'Engine':
                        $this->setEngine($v);
                        break;
                    case 'Rows':
                        $this->rows_qty = (int) $v;
                        break;
                    case 'Data_length':
                        $this->size = (int) $v;
                        break;
                }
            }
            return $this->cache['load'] = true;
        }

        protected function loadColumns(): array {
            $res = [];
            if (!$this->exists()) {
                return $res;
            }
            $db    = $this->getDb();
            $class = $db->getNameSpace() . '\Column';
            foreach (
                $db->fetchRows('SHOW FULL COLUMNS FROM ' . $db->t($this->getName())
            ) as $params) {
                $type_length = explode('(', rtrim($params['Type'], ')'));
                $column      = $class::init($params['Field'], $this);
                $column->setIsAutoIncrement(
                    stripos('auto_increment', $params['Extra']) !== false
                );
                $column->setCollation((string) $params['Collation']);
                $column->setDefaultValue($params['Default']);
                $column->setLength($type_length[1] ?? '');
                $column->setIsNull($params['Null'] != 'NO');
                $column->setIsPrimaryKey($params['Key'] == 'PRI');
                $column->setType((string) $type_length[0]);
                $res[$column->getName()] = $column;
            }
            return $res;
        }

    }
