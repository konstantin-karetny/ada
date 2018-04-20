<?php
    /**
    * @package   project/core
    * @version   1.0.0 20.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\MySQL;

    class Driver extends \Ada\Core\Db\Driver {

        protected
            $charset     = 'utf8mb4',
            $dsn_format  = '%driver%:host=%host%;dbname=%name%;charset=%charset%',
            $min_version = '5.7.0',
            $port        = 3306,
            $user        = 'root';

    }
