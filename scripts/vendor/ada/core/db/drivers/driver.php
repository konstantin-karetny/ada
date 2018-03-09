<?php
    /**
    * @package   ada/core
    * @version   1.0.0 09.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db\Drivers;

    class Driver extends \Ada\Core\Singleton {

        const
            INP_PARAM_CHAR = ':',
            Q              = '`';

        protected
            $charset       = 'utf8',
            $driver        = 'mysql',
            $dsn_format    = '%driver%:host=%host%;dbname=%name%;charset=%charset%',
            $host          = 'localhost',
            $name          = '',
            $password      = '',
            $pdo           = null,
            $pdo_params    = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
            ],
            $prefix        = '',
            $stmt          = null,
            $user          = 'root';

        public static function init(string $id = '', bool $cached = true): self {
            return parent::init($id, $cached, $args);
        }

        public function connect(): bool {
            try {
                $this->pdo = new \PDO(
                    $this->getDsn(),
                    $this->getUser(),
                    $this->getPassword(),
                    $this->getPdoParams()
                );
            } catch (PDOException $e) {
                throw new \Ada\Core\Exception(1, $e->getMessage());
            }
            return $this->isConnected();
        }

        public function disconnect(): bool {
            $this->pdo  = null;
            $this->stmt = null;
            return !$this->isConnected();
        }

        public function esc(string $val) {
            if (is_numeric($val)) {
                return $val * 1;
            }
            return (
                static::INP_PARAM_CHAR .
                str_replace(
                    static::INP_PARAM_CHAR,
                    '\\' . static::INP_PARAM_CHAR . '\\',
                    $val
                ) .
                static::INP_PARAM_CHAR
            );
        }

        public function execute(string $query): bool {
            if (!$this->isConnected()) {
                $this->connect();
            }
            $pattern = (
                '/\s' .
                static::INP_PARAM_CHAR . '(.+)' . static::INP_PARAM_CHAR .
                '\s/U'
            );
            $inp_params = [];
            preg_match_all($pattern, $query, $inp_params);
            $inp_params = (array) $inp_params[1];
            $this->stmt = $this->pdo->prepare(
                trim(
                    preg_replace(
                        [
                            '/\s+/',
                            $pattern
                        ],
                        [
                            ' ',
                            ' ? '
                        ],
                        $query
                    )
                )
            );
            return (bool) $this->stmt->execute($inp_params);
        }

        public function selectCell(
            string $query,
            string $filter  = 'auto',
            string $default = null
        ) {
            $res = reset($this->selectRow($query, \PDO::FETCH_NUM, []));
            return \Ada\Core\Clean::value($res === false ? $default : $res);
        }

        public function selectRow(
            string $query,
            int    $fetch_style = null,
                   $default     = null
        ) {
            $this->execute($query);
            $res = $this->stmt->fetch($fetch_style);
            return \Ada\Core\Clean::values($res === false ? $default : $res);
        }

        public function selectRows(
            string $query,
            int    $fetch_style = null,
            string $key         = '',
            array  $default     = []
        ): array {
            $this->execute($query);
            $res = $this->stmt->fetchAll($fetch_style);
            $res = \Ada\Core\Clean::values($res === false ? $default : $res);
            if ($key === '') {
                return $res;
            }
            if (!key_exists($key, (array) reset($res))) {
                throw new \Ada\Core\Exception(
                    2,
                    '. Key \'' . $key . '\'. Query \'' . $query . '\''
                );
            }
            return array_combine(
                array_column((array) $res, $key),
                array_values($res)
            );
        }

        public function q(string $name, string $as = ''): string {
            return (
                (
                    strpos($name, '.') === false
                        ? (static::Q . $name . static::Q)
                        : implode(
                            '.',
                            array_map(
                                function($el) {
                                    return static::Q . $el . static::Q;
                                },
                                explode('.', $name)
                            )
                        )
                ) .
                (
                    $as ? (' AS ' . static::Q . $as . static::Q) : ''
                )
            );
        }

        public function qs(array $names): string {
            return implode(
                ', ',
                array_map(
                    function($name, $as) {
                        return is_int($name)
                            ? $this->q($as)
                            : $this->q($name, $as);
                    },
                    array_keys($names),
                    array_values($names)
                )
            );
        }

        public function t(string $table, string $as = ''): string {
            return $this->q($this->getPrefix() . $table, $as);
        }

        public function getCharset(): string {
            return $this->charset;
        }

        public function isConnected(): bool {
            return $this->pdo instanceof \PDO;
        }

        public function getDriver(): string {
            return $this->driver;
        }

        public function getDsn(): string {
            $props = get_object_vars($this);
            return str_replace(
                array_map(
                    function($el) {
                        return '%' . $el . '%';
                    },
                    array_keys($props)
                ),
                array_values($props),
                $this->getDsnFormat()
            );
        }

        public function getDsnFormat(): string {
            return $this->dsn_format;
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

        public function getPdoParams(): array {
            return $this->pdo_params;
        }

        public function getPrefix(): string {
            return $this->prefix;
        }

        public function getUser(): string {
            return $this->user;
        }

        public function setCharset(string $charset) {
            $this->charset = $charset;
        }

        public function setHost(string $host) {
            $this->host = $host;
        }

        public function setName(string $name) {
            $this->name = $name;
        }

        public function setPassword(string $password) {
            $this->password = $password;
        }

        public function setPdoParams(array $pdo_params) {
            $this->pdo_params = $this->pdo_params + $pdo_params;
        }

        public function setPrefix(string $prefix) {
            $this->prefix = $prefix;
        }

        public function setUser(string $user) {
            $this->user = $user;
        }

    }
