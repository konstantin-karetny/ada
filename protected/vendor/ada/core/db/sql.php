<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Sql extends \Ada\Core\Proto {

        protected
            $db = null;

        public static function init(\Ada\Core\Db\Driver $db): \Ada\Core\Db\Sql {
            $class = get_called_class();
            if (get_parent_class($class) == __CLASS__) {
                $slash        = strripos($class, '\\');
                $driver_class = (
                    substr($class, 0, $slash + 1) .
                    $db->getDriver() .
                    substr($class, $slash)
                );
                if (class_exists($driver_class)) {
                    return $driver_class::init($db);
                }
            }
            return new static($db);
        }

        protected function __construct(\Ada\Core\Db\Driver $db) {
            $this->db = $db;
        }

        public function getDb(): \Ada\Core\Db\Driver {
            return $this->db;
        }

    }
