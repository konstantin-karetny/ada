<?php
    /**
    * @package   project/core
    * @version   1.0.0 21.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\MySQL;

    class Driver extends \Ada\Core\Db\Driver {

        protected
            $min_version = '5.7.0';

        public static function init(array $params): self {
            return new static($params);
        }

    }
