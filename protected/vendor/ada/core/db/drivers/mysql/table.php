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
                        !$column->getDefaultValue()
                            ? ''
                            : ' DEFAULT ' . $db->e($column->getDefaultValue())
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
            $this->cache = [];
            $this->cache();
            $this->setProps($this->cache);
            return true;
        }

        protected function cache(): bool {
            if (
                isset(
                    $this->cache['collation'],
                    $this->cache['engine']
                )
            ) {
                return true;
            }
            $db  = $this->getDb();
            $row = $db->fetchRow(
                'SHOW TABLE STATUS LIKE ' . $db->e($this->getName(true))
            );
            if (!$row) {
                return false;
            }
            $this->cache = array_merge(
                $this->cache,
                [
                    'collation' => (string) $row['Collation'],
                    'engine'    => (string) $row['Engine'],
                    'exists'    => true
                ]
            );
            return true;
        }

        protected function cacheColumns(): bool {
            if (isset($this->cache['columns'])) {
                return true;
            }
            if (!$this->exists()) {
                return false;
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
            return true;
        }

    }
