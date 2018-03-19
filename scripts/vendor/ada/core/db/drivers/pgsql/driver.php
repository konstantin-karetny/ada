<?php
    /**
    * @package   ada/core
    * @version   1.0.0 19.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers\PgSQL;

    class Driver extends \Ada\Core\Db\Driver {

        protected
            $min_version = '10.0';

        public static function init(int $id = 0, array $presets = []): self {
            return parent::init($id, $presets);
        }

    }
