<?php
    /**
    * @package   project/core
    * @version   1.0.0 05.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Sql\PgSQL;

    class Driver extends \Ada\Core\Db\Sql {

        public function lcСollate(): string {
            return 'SHOW LC_COLLATE';
        }

        public function searchPath(): string {
            return 'SHOW search_path';
        }

        public function serverEncoding(): string {
            return 'SHOW SERVER_ENCODING';
        }

    }
