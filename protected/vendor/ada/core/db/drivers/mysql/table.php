<?php
    /**
    * @package   project/core
    * @version   1.0.0 28.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\MySQL;

    class Table extends \Ada\Core\Db\Table {

        protected
            $collation = '',
            $engine    = 'InnoDB';

        protected function __construct(string $name, Driver $db, bool $cached) {
            $this->collation = $db->getCollation();
            parent::__construct($name, $db, $cached);
        }

        public function getCollation(): string {
            return $this->collation;
        }

        public function getEngine(): string {
            return $this->engine;
        }

        public function setCollation(string $collation) {
            $this->collation = \Ada\Core\Clean::cmd($collation);
        }

        public function setEngine(string $engine) {
            $this->engine = \Ada\Core\Clean::cmd($engine);
        }

        protected function load(bool $cached = true): bool {
            $cache = $this->cache(__FUNCTION__);
            if ($cached && $cache) {
                $load = $cache;
            }
            else {
                $db  = $this->getDb();
                $row = $db->fetchRow(
                    'SHOW TABLE STATUS LIKE ' . $db->esc($this->getName(true))
                );
                if (!$row) {
                    return false;
                }
                $load = [
                    'collation' => (string) $row['Collation'],
                    'engine'    => (string) $row['Engine']
                ];
                $this->cache(__FUNCTION__, $load);
            }
            foreach ($load as $k => $v) {
                $this->{'set' . \Ada\Core\Strings::toCamelCase($k)}($v);
            }
            return true;
        }

        protected function loadColumns(bool $cached = true): bool {
            if (!$this->exists()) {
                return false;
            }
            $cache = $this->cache(__FUNCTION__);
            if ($cached && $cache) {
                $columns = $cache;
            }
            else {
                $db      = $this->getDb();
                $columns = [];
                foreach ($db->fetchRows(
                    'SHOW FULL COLUMNS FROM ' . $db->t($this->getName())
                ) as $row) {
                    $type_length = explode('(', rtrim($row['Type'], ')'));
                    $column      = Column::init($row['Field'], $this);
                    $column->setIsAutoIncrement(
                        stripos('auto_increment', $row['Extra']) !== false
                    );
                    $column->setCollation((string) $row['Collation']);
                    $column->setDefaultValue($row['Default']);
                    $column->setLength($type_length[1] ?? '');
                    $column->setIsNull(
                        strtoupper(trim($row['Null'])) != 'NO'
                    );
                    $column->setIsPrimaryKey(
                        strtoupper(trim($row['Key'])) == 'PRI'
                    );
                    $column->setType($type_length[0]);
                    $columns[] = $column;
                }
                $this->cache(__FUNCTION__, $columns);
            }
            $this->setColumns($columns);
            return true;
        }

    }
