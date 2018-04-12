<?php
    /**
    * @package   project/core
    * @version   1.0.0 13.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Driver extends \Ada\Core\Db\Driver {

        protected
            $min_version = '9.6',
            $dsn_format  = '%driver%:host=%host%;port=%port%;dbname=%name%;user=%user%;password=%password%',
            $port        = 5432,
            $user        = 'postgres',
            $quote       = '"';

        public static function init(array $params): self {
            return new static($params);
        }

        protected function getProps(): array {
            return [
                'charset'   => trim($this->fetchCell('SHOW SERVER_ENCODING')),
                'collation' => trim($this->fetchCell('SHOW LC_COLLATE')),
                'schema'    => trim(
                    explode(' ', $this->fetchCell('SHOW search_path'))[0]
                ),
                'version'   => trim($this->getAttribute(
                    \PDO::ATTR_SERVER_VERSION
                ))
            ];
        }

    }
