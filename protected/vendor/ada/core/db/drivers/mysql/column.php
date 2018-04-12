<?php
    /**
    * @package   project/core
    * @version   1.0.0 13.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\MySQL;

    class Column extends \Ada\Core\Db\Column {

        public static function create($table, array $params): self {
            $res   = [];
            $db    = $this->getDb();
            $table = $this->getTable();
            $rea[] = $db->exec('
                ALTER TABLE ' . $db->t($table->getName()) . '
                ADD '         . $db->q($this->getName()) . ' ' .
                $this->getType() .
                (
                    !$this->getLength()
                        ? ''
                        : '(' . $db->e($this->getLength()) . ')'
                ) .
                (
                    $this->getDefaultValue() === ''
                        ? ''
                        : ' DEFAULT ' . $this->getDefaultValue()
                ) .
                (
                    !$this->getCollation()
                        ? ''
                        : ' COLLATE ' . $db->e($this->getCollation())
                ) .
                (
                    ($this->getIsNull() ? '' : ' NOT') . ' NULL'
                ) .
                (
                    $this->getIsAutoIncrement() ? ' AUTO_INCREMENT' : ''
                ) .
                (
                    !$after
                        ? ''
                        : ' AFTER ' . $db->q($after->getName())
                )
            );
            if ($this->getIsPrimaryKey()) {
                $res[] = $db->exec('
                    ALTER TABLE '      . $db->t($table->getName()) . '
                    ADD PRIMARY KEY (' . $db->q($this->getName()) . ')
                ');
            }
            if ($this->getIsPrimaryKey()) {
                $res[] = $db->exec('
                    ALTER TABLE ' . $db->t($table->getName()) . '
                    ADD UNIQUE (' . $db->q($this->getName()) . ')
                ');
            }
            return !in_array(false, $res);
        }

        protected function getProps(): array {
            $table = $this->getTable();
            $db    = $table->getDb();
            $row   = $db->fetchRow('
                SHOW FULL COLUMNS
                FROM ' . $db->t($table->getName()) . '
                LIKE ' . $db->e($this->getName())
            );
            if (!$row) {
                return [];
            }
            $key         = strtolower(trim($row['Key']));
            $type_length = explode('(', rtrim($row['Type'], ')'));
            $res         = [
                'collation'         => trim($row['Collation']),
                'default_value'     => $row['Default'],
                'is_auto_increment' => (
                    stripos('auto_increment', strtolower($row['Extra'])) !== false
                ),
                'is_nullable'       => strtolower(trim($row['Null'])) == 'yes',
                'is_primary_key'    => $key                           == 'pri',
                'is_unique_key'     => $key                           == 'uni',
                'length'            => (int) ($type_length[1] ?? 0),
                'type'              => trim($type_length[0])
            ];
            return $res;

        }

    }
