<?php
    /**
    * @package   project/core
    * @version   1.0.0 05.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Driver extends \Ada\Core\Db\Driver {

        protected
            $dsn_format  = '%driver%:host=%host%;port=%port%;dbname=%name%;user=%user%;password=%password%',
            $min_version = '9.6',
            $port        = 5432,
            $quote       = '"',
            $schema      = 'public',
            $user        = 'postgres';

        protected function extractParams(): array {
            $sql         = \Ada\Core\Db\Sql\PgSQL\Driver::init($this);
            $search_path = explode(' ', $this->fetchCell($sql->searchPath()));
            return [
                'charset'   => trim($this->fetchCell($sql->serverEncoding())),
                'collation' => trim($this->fetchCell($sql->lcСollate())),
                'schema'    => trim(end($search_path)),
                'version'   => trim($this->getAttribute(\PDO::ATTR_SERVER_VERSION))
            ];
        }

    }
