<?php
    /**
    * @package   ada/core
    * @version   1.0.0 17.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Table extends \Ada\Core\Proto {

        use \Ada\Core\Traits\Singleton;

        protected
            $columns = [],
            $db      = null,
            $name    = '';

        public static function init(string $name, Driver $db) {
            return static::initSingleton(
                $db->getPrefix() . $name,
                true,
                ...func_get_args()
            );
        }

        protected function __construct(string $name, Driver $db) {
            $this->db      = $db;
            $this->name    = \Ada\Core\Clean::cmd($name);
            $this->columns = $this->detectColumns();
        }

        public function getColumn(string $name): Column {
            if (isset($this->columns[$name])) {
                return $this->columns[$name];
            }
            $class = $this->getDb()->getNameSpace() . '\Column';
            return new $class($this, $name);
        }

        public function getColumns(): array {
            return $this->columns;
        }

        public function getDb(): Driver {
            return $this->db;
        }

        public function getName(): string {
            return $this->name;
        }

        protected function detectColumns(): array {
            $res = [];
            $db  = $this->getDb();
            foreach (
                $db->fetchRows('SHOW FULL COLUMNS FROM ' . $db->t($this->getName())
            ) as $params) {
                $type_length = explode('(', rtrim($params['Type'], ')'));
                $column      = $this->getColumn($params['Field']);
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
