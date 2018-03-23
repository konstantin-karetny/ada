<?php
    /**
    * @package   project/core
    * @version   1.0.0 21.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Driver extends \Ada\Core\Db\Driver {

        protected
            $min_version = '9.6',
            $dsn_format  = '%driver%:host=%host%;port=%port%;dbname=%name%;user=%user%;password=%password%',
            $port        = 5432;

        public static function init(array $params): self {
            return new static($params);
        }

        protected function detectCollation(): string {
            return $this->fetchCell('
                SELECT ' . $this->q('DEFAULT_COLLATION_NAME') . '
                FROM '   . $this->q('INFORMATION_SCHEMA.SCHEMATA') . '
                WHERE '  . $this->q('SCHEMA_NAME') . '
                LIKE '   . $this->esc($this->getName()) . '
            ');
        }

    }
