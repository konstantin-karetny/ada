<?php
    /**
    * @package   ada/core
    * @version   1.0.0 21.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Table extends \Ada\Core\Proto {

        use \Ada\Core\Traits\Singleton;

        protected
            $columns = null,
            $db      = null,
            $name    = '';

        public static function init(string $name, $db) {
            return static::initSingleton(
                $db->getPrefix() . $name,
                true,
                ...func_get_args()
            );
        }

        protected function __construct(string $name, Driver $db) {
            $this->db   = $db;
            $this->id   =
            $this->name = \Ada\Core\Clean::cmd($name);
        }

        public function getColumn(string $name): Column {
            $columns = $this->getColumns();
            return $columns[$name];
        }

        public function getColumns(): array {
            return
                $this->columns === null
                    ? $this->columns = $this->detectColumns()
                    : $this->columns;
        }

        public function getDb(): Driver {
            return $this->db;
        }

        public function getName(): string {
            return $this->name;
        }

        protected function detectColumns(): array {
            $res   = [];
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