<?php
    /**
    * @package   project/core
    * @version   1.0.0 05.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Sql;

    class Table extends \Ada\Core\Db\Sql {

        public function indexes(\Ada\Core\Db\Table $table): string {
            $db = $this->getDb();
            return \Ada\Core\Str::toOneLine('
                SHOW INDEX
                FROM '  . $db->t($table->getName()) . '
                WHERE ' . $db->q('Non_unique')      . ' = 0
            ');
        }

    }
