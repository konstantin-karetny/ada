<?php
    /**
    * @package   project/core
    * @version   1.0.0 20.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\MySQL;

    class Table extends \Ada\Core\Db\Table {

        protected
            $engine = 'InnoDB';

        protected static function getQueryCreate($db, array $params): string {
            return (
                parent::getQueryCreate($db, $params) .
                (!$params['engine']    ? '' : ' ENGINE = '          . $db->e($params['engine'])) .
                (!$params['charset']   ? '' : ' DEFAULT CHARSET = ' . $db->e($params['charset'])) .
                (!$params['collation'] ? '' : ' COLLATE = '         . $db->e($params['collation']))
            );
        }

    }
