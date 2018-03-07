<?php
    /**
    * @package   ada/core
    * @version   1.0.0 07.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Db extends Singleton {

        protected
            $charset     = 'utf8',
            $connected   = false,
            $driver      = null,
            $driver_name = 'mysql',
            $host        = 'localhost',
            $name        = '',
            $password    = '',
            $prefix      = '',
            $user        = 'root';

        public static function init(string $id = '', bool $cached = true): self {
            return parent::init($id, $cached);
        }

        protected function __construct(string $id, bool $cached = true) {
            parent::__construct($id, $cached);
        }

        public function connect(): string {
            if ($this->isConnected()) {
                return true;
            }
            $this->driver = $this->getDriver();


            exit(var_dump( $this->driver ));

            switch ($this->type) {
                case 'mysql':
                    $dsn = $this->type . ':' . 'host=' . $this->host . ';dbname=' . $this->name . ';charset=' . $this->charset;
                    break;
            }
            try {
                $pdo = new PDO($dsn, $this->user, $this->password, $this->pdo_options);
            } catch (PDOException $e) {
                throw new \Exception('Unable to connect to database. ' . $e->getMessage());
            }
            return $pdo;


        }

        public function loadCell(string $query, $default = ''): string {
            if (!$this->connected) {
                $this->connect();
            }
            exit(var_dump( $query ));
        }

        public function getCharset(): string {
            return $this->charset;
        }

        public function isConnected(): bool {
            return $this->connected;
        }

        public function getDriverName(): string {
            return $this->driver_name;
        }

        public function getHost(): string {
            return $this->host;
        }

        public function getName(): string {
            return $this->name;
        }

        public function getPassword(): string {
            return $this->password;
        }

        public function getPrefix(): string {
            return $this->prefix;
        }

        public function getUser(): string {
            return $this->user;
        }

        public function setCharset(string $charset): string {
            $this->charset = $charset;
        }

        public function setDriverName(string $driver_name): string {
            $this->driver_name = $driver_name;
        }

        public function setHost(string $host): string {
            $this->host = $host;
        }

        public function setName(string $name): string {
            $this->name = $name;
        }

        public function setPassword(string $password): string {
            $this->password = $password;
        }

        public function setPrefix(string $prefix): string {
            $this->prefix = $prefix;
        }

        public function setUser(string $user): string {
            $this->user = $user;
        }

        protected function getDriver(bool $cached = true): Db\Drivers\Driver {
            if ($this->driver && $cached) {
                return $this->driver;
            }
            $classname    = (
                __NAMESPACE__ .
                '\\Db\\Drivers\\' .
                ucfirst($this->getDriverName())
            );
            return new $classname();
        }

    }
