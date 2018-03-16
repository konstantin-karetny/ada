<?php
    /**
    * @package   ada/core
    * @version   1.0.0 16.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    class Table extends \Ada\Core\Proto {

        protected
            $columns = [],
            $db      = null,
            $name    = '';

        public static function init(
            string         $name,
            Drivers\Driver $db
        ): self {
            static $res;
            return $res ?? $res = new self($name, $db);
        }

        protected function __construct(
            string         $name,
            Drivers\Driver $db
        ) {
            $this->db   = $db;
            $this->name = trim($name);
            foreach (
                $db->fetchRows('SHOW FULL COLUMNS FROM ' . $db->t($this->name)
            ) as $params) {
                $type_length = explode('(', rtrim($params['Type'], ')'));
                $column      = new Column($this, (string) $params['Field']);
                $column->setIsAutoIncrement(
                    stripos('auto_increment', $params['Extra']) !== false
                );
                $column->setCollation((string) $params['Collation']);
                $column->setDefaultValue($params['Default']);
                $column->setLength($type_length[1] ?? '');
                $column->setIsNull($params['Null'] != 'NO');
                $column->setIsPrimaryKey($params['Key'] == 'PRI');
                $column->setType((string) $type_length[0]);
                $this->columns[$column->getName()] = $column;
            }
        }

        public function getColumn(string $name): Column {
            if (isset($this->columns[$name])) {
                return $this->columns[$name];
            }
            $res = new Column($this, $name);
            return $res;
        }

        public function getColumns(): array {
            return $this->columns;
        }

        public function getDb(): Drivers\Driver {
            return $this->db;
        }

        public function getName(): string {
            return $this->name;
        }

    }
