<?php
    /**
    * @package   project/core
    * @version   1.0.0 21.03.2018
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

        protected function load(bool $cached): bool {
            if ($cached && $this->cache(__FUNCTION__)) {
                return $this->cache(__FUNCTION__);
            }
            $db   = $this->getDb();
            $load = (array) $db->fetchRow(
                'SHOW TABLE STATUS LIKE ' . $db->esc($this->getName(true))
            );
            if (!$load) {
                return false;
            }
            foreach ($load as $k => $v) {
                switch ($k) {
                    case 'Collation':
                        $this->setCollation($v);
                        break;
                    case 'Engine':
                        $this->setEngine($v);
                        break;
                }
            }
            $this->cache(__FUNCTION__, $load);
            return true;
        }

        protected function loadColumns(): array {
            $res = [];
            if (!$this->exists()) {
                return $res;
            }
            $db = $this->getDb();
            foreach ($db->fetchRows('
                SHOW FULL COLUMNS FROM ' . $db->t($this->getName())
            ) as $params) {
                $type_length = explode('(', rtrim($params['Type'], ')'));
                $column      = Column::init($params['Field'], $this);
                $column->setIsAutoIncrement(
                    stripos('auto_increment', $params['Extra']) !== false
                );
                $column->setCollation($params['Collation']);
                $column->setDefaultValue($params['Default']);
                $column->setLength($type_length[1] ?? '');
                $column->setIsNull(
                    strtoupper(trim($params['Null'])) != 'NO'
                );
                $column->setIsPrimaryKey(
                    strtoupper(trim($params['Key'])) == 'PRI'
                );
                $column->setType($type_length[0]);
                $res[$column->getName()] = $column;
            }
            return $res;
        }

    }
