<?php
    /**
    * @package   project/core
    * @version   1.0.0 22.05.2018
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
            $search_path = explode(
                ' ',
                $this->fetchCell(
                    $this->getQuery()->raw('SHOW search_path')
                )
            );
            return [
                'charset'   => trim($this->fetchCell('SHOW SERVER_ENCODING')),
                'collation' => trim($this->fetchCell('SHOW LC_COLLATE')),
                'schema'    => trim(end($search_path)),
                'version'   => trim($this->getAttribute(\PDO::ATTR_SERVER_VERSION))
            ];
        }

    }
