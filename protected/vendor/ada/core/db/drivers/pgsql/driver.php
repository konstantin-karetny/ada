<?php
    /**
    * @package   project/core
    * @version   1.0.0 23.03.2018
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

        protected function load(): bool {
            $this->charset   = $this->fetchCell('SHOW SERVER_ENCODING');
            $this->collation = $this->fetchCell('SHOW LC_COLLATE');
            $this->version   = (string) $this->getAttribute(
                \PDO::ATTR_SERVER_VERSION
            );
            return true;
        }

    }
