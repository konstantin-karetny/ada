<?php
    /**
    * @package   project/core
    * @version   1.0.0 23.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\MySQL;

    class Table extends \Ada\Core\Db\Table {

        protected
            $engine = 'InnoDB';

        protected function getQueryCreate(): string {
            $db = $this->getDb();
            return (
                parent::getQueryCreate() .
                (!$params['engine']    ? '' : ' ENGINE = '          . $db->e($this->getEngine())) .
                (!$params['charset']   ? '' : ' DEFAULT CHARSET = ' . $db->e($this->getCharset())) .
                (!$params['collation'] ? '' : ' COLLATE = '         . $db->e($this->getCollation()))
            );
        }

    }
