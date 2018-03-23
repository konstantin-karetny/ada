<?php
    /**
    * @package   project/core
    * @version   1.0.0 23.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\MySQL;

    class Driver extends \Ada\Core\Db\Driver {

        protected
            $charset     = 'utf8mb4',
            $min_version = '5.7.0',
            $dsn_format  = '%driver%:host=%host%;dbname=%name%;charset=%charset%',
            $port        = 3306,
            $user        = 'root';

        public static function init(array $params): self {
            return new static($params);
        }

        protected function load(): bool {
            parent::load();
            $res = $this->fetchRow('
                SELECT ' .
                    $this->q('DEFAULT_CHARACTER_SET_NAME') . ', ' .
                    $this->q('DEFAULT_COLLATION_NAME') . '
                FROM '   . $this->q('INFORMATION_SCHEMA.SCHEMATA') . '
                WHERE '  . $this->q('SCHEMA_NAME') . '
                LIKE '   . $this->esc($this->getName()) . '
            ');
            $this->charset   = $res['DEFAULT_CHARACTER_SET_NAME'];
            $this->collation = $res['DEFAULT_COLLATION_NAME'];
            return true;
        }

    }
