<?php
    /**
    * @package   project/core
    * @version   1.0.0 30.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\MySQL;

    class Table extends \Ada\Core\Db\Table {

        protected
            $engine = 'InnoDB';

        protected function __construct(
            string $name,
            Driver $db,
            bool   $cached = true
        ) {
            $this->collation = $db->getCollation();
            parent::__construct($name, $db, $cached);
        }

        public function create(): bool {
            $db          = $this->getDb();
            $query       = 'CREATE TABLE ' . $db->t($this->getName()) . ' (';
            $constraints = '';
            foreach ($this->getColumns() as $column) {
                $query .= '
                    ' . (
                    $db->q($column->getName()) . ' ' .
                    $column->getType() .
                    (
                        !$column->getLength()
                            ? ''
                            : '(' . $db->e($column->getLength()) . ')'
                    ) .
                    (
                        $column->getDefaultValue() === ''
                            ? ''
                            : ' DEFAULT ' . $column->getDefaultValue()
                    ) .
                    (
                        !$column->getCollation()
                            ? ''
                            : ' COLLATE ' . $db->e($column->getCollation())
                    ) .
                    (
                        ($column->getIsNull() ? '' : ' NOT') . ' NULL'
                    ) .
                    (
                        $column->getIsAutoIncrement() ? ' AUTO_INCREMENT' : ''
                    )
                ) . ',';
                if ($column->getIsPrimaryKey()) {
                    $constraints .= ('
                        PRIMARY KEY (' . $db->q($column->getName()) . '),
                    ');
                }
                if ($column->getIsUniqueKey()) {
                    $constraints .= ('
                        UNIQUE KEY ('  . $db->q($column->getName()) . '),
                    ');
                }
            }
            $query = (
                rtrim($query, " \t\n\r\0\x0B,") .
                (
                    !$constraints
                        ? ''
                        : ',' . rtrim($constraints, " \t\n\r\0\x0B,")
                ) . '
                )' .
                (
                    !$this->getEngine()
                        ? ''
                        : ' ENGINE = ' . $db->e($this->getEngine())
                ) .
                (
                    !$this->getCharset()
                        ? ''
                        : ' DEFAULT CHARSET = ' . $db->e($this->getCharset())
                ) .
                (
                    !$this->getCollation()
                        ? ''
                        : ' COLLATE = ' . $db->e($this->getCollation())
                )
            );
            if (!$db->exec($query)) {
                return false;
            }
            $this->reInit();
            return true;
        }

        protected function fetchColumns(bool $cached = true): array {
            if ($cached && isset($this->cache['columns'])) {
                return $this->cache['columns'];
            }
            if (!$this->exists()) {
                return [];
            }
            $db                     = $this->getDb();
            $this->cache['columns'] = [];
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
                $column->setIsUniqueKey(
                    strtoupper(trim($row['Key'])) == 'UNI'
                );
                $column->setType($type_length[0]);
                $this->cache['columns'][$column->getName()] = $column;
            }
            return $this->cache['columns'];
        }

        protected function fetchDbData(bool $cached = true): array {
            $keys = [
                'charset',
                'collation',
                'engine',
                'exists',
                'schema'
            ];
            if ($cached && \Ada\Core\Arr::keysExist($this->cache, $keys)) {
                return array_intersect_key($this->cache, array_flip($keys));
            }
            $db  = $this->getDb();
            $row = $db->fetchRow('
                SELECT *
                FROM '  . $db->q('information_schema.TABLES', 't') . '
                JOIN '  . $db->q('information_schema.COLLATION_CHARACTER_SET_APPLICABILITY', 'ccsa') . '
                ON '    . $db->q('ccsa.COLLATION_NAME') . ' = '    . $db->q('t.TABLE_COLLATION') . '
                WHERE ' . $db->q('t.TABLE_SCHEMA')      . ' LIKE ' . $db->e($db->getName()) . '
                AND '   . $db->q('t.TABLE_NAME')        . ' LIKE ' . $db->e($this->getName(true)) . '
            ');
            if (!$row) {
                return [];
            }
            $res = [
                'charset'   => (string) $row['CHARACTER_SET_NAME'],
                'collation' => (string) $row['COLLATION_NAME'],
                'engine'    => (string) $row['ENGINE'],
                'exists'    => true,
                'schema'    => (string) $row['TABLE_SCHEMA']
            ];
            $this->cache = array_merge($this->cache, $res);
            return $res;
        }

    }
