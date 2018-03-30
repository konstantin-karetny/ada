<?php
    /**
    * @package   project/core
    * @version   1.0.0 30.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Column extends \Ada\Core\Db\Column {

        public function add(): bool {
            $res   = [];
            $db    = $this->getDb();
            $table = $this->getTable();
            $rea[] = $db->exec('
                ALTER TABLE ' . $db->t($table->getName()) . '
                ADD '         . $db->q($this->getName()) . ' ' .
                (
                    $this->getIsAutoIncrement()
                        ? 'serial'
                        : (
                            $this->getType() .
                            (
                                !$this->getLength()
                                    ? ''
                                    : '(' . $db->e($this->getLength()) . ')'
                            ) .
                            (
                                $this->getDefaultValue() === null
                                    ? ''
                                    : ' DEFAULT ' . $this->getDefaultValue()
                            ) .
                            (
                                !$this->getCollation()
                                    ? ''
                                    : ' COLLATE ' . $db->e($this->getCollation())
                            )
                        )
                ) .
                (
                    ($this->getIsNull() ? '' : ' NOT') . ' NULL'
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

    }
