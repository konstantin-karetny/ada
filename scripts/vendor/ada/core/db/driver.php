<?php
    /**
    * @package   ada/core
    * @version   1.0.0 21.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Driver extends \Ada\Core\Proto {

        const
            ESC_TAG      = ':',
            ADD_PARAMS   = [
                'attributes',
                'charset',
                'date_format',
                'dsn_format',
                'driver',
                'host',
                'name',
                'password',
                'prefix',
                'quote',
                'user'
            ];

        protected
            $attributes  = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ],
            $charset     = 'utf8mb4',
            $collation   = null,
            $date_format = 'Y-m-d H:i:s',
            $dsn_format  = '%driver%:host=%host%;dbname=%name%;charset=%charset%',
            $driver      = 'mysql',
            $fetch_mode  = [],
            $host        = '127.0.0.1',
            $min_version = '',
            $name        = '',
            $password    = '',
            /** @var \PDO */
            $pdo         = null,
            $prefix      = '',
            $quote       = '`',
            /** @var \PDOStatement */
            $stmt        = null,
            $user        = 'root';

        public static function init(array $params) {
            return new static($params);
        }

        protected function __construct(array $params) {
            foreach (array_intersect_key(
                $params,
                array_flip(static::ADD_PARAMS)
            ) as $k => $v) {
                $this->$k = \Ada\Core\Type::set(
                    $v,
                    \Ada\Core\Type::get($this->$k)
                );
            }
            if (
                version_compare(
                    $this->getVersion(),
                    $this->getMinVersion(),
                    '<'
                )
            ) {
                throw new \Ada\Core\Exception(
                    (
                        'Version of the driver ' . $this->getVersion() .
                        ' less than required '   . $this->getMinVersion()
                    ),
                    1
                );
            }
        }

        public function closeTransaction(): bool {
            if (!$this->isTransactionOpen()) {
                return false;
            }
            try {
                return (bool) $this->pdo->commit();
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    'Failed to close a transaction. ' . $e->getMessage(),
                    8
                );
            }
        }

        public function connect(): bool {
            if ($this->isConnected()) {
                return true;
            }
            $error = 'Failed to connect to a database';
            if ($this->getName() === '') {
                throw new \Ada\Core\Exception(
                    $error . '. No database name',
                    1
                );
            }
            try {
                $this->pdo = new \PDO(
                    $this->getDsnLine(),
                    $this->getUser(),
                    $this->getPassword(),
                    $this->getAttributes()
                );
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    $error . '. ' . $e->getMessage(),
                    1
                );
            }
            return $this->isConnected();
        }

        public function debugInfo(): array {
            if (!$this->stmt) {
                return [];
            }
            ob_start();
            $this->stmt->debugDumpParams();
            return explode("\n", trim(ob_get_clean()));
        }

        public function delete(string $table, string $condition): bool {
            return $this->exec(
                'DELETE FROM ' .
                $this->t($table) .
                'WHERE ' . $condition
            );
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
                ' ' . static::ESC_TAG .
                str_replace(
                    static::ESC_TAG,
                    '\\' . static::ESC_TAG . '\\',
                    $val
                ) .
                static::ESC_TAG . ' '
            );
        }

        public function exec(string $query): bool {
            if (!$this->isConnected()) {
                $this->connect();
            }
            $pattern    = (
                '/\s' . static::ESC_TAG . '(.+)' . static::ESC_TAG . '\s/U'
            );
            $inp_params = [];
            preg_match_all($pattern, $query, $inp_params);
            $inp_params = array_map(
                function($el) {
                    return str_replace(
                        '\\' . static::ESC_TAG . '\\',
                        static::ESC_TAG,
                        $el
                    );
                },
                (array) $inp_params[1]
            );
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
            if ($this->fetch_mode) {
                $this->stmt->setFetchMode(...$this->fetch_mode);
            }
            try {
                $res = (bool) $this->stmt->execute($inp_params);
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    (
                        'Failed to execute a database query. ' .
                        $e->getMessage() . '. ' .
                        'Query: \'' . trim($query) . '\''
                    ),
                    3
                );
            }
            $this->fetch_mode = [];
            $this->stmt->setFetchMode(
                $this->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE)
            );
            return $res;
        }

        public function fetchCell(
            string $query,
            string $type    = 'auto',
            string $default = null
        ) {
            $res = reset($this->fetchRow($query, \PDO::FETCH_NUM, []));
            return \Ada\Core\Type::set(
                $res === false ? $default : $res,
                $type
            );
        }

        public function fetchColumn(
            string $query,
            string $column,
            string $key     = '',
            array  $default = []
        ): array {
            $this->exec($query);
            $res = $this->fetchRows($query, \PDO::FETCH_ASSOC, $key);
            if (!key_exists($column, reset($res))) {
                throw new \Ada\Core\Exception(
                    'Unknown column \'' . $column . '\'. Query: \'' . trim($query) . '\'',
                    6
                );
            }
            return array_combine(
                array_keys($res),
                array_column($res, $column)
            );
        }

        public function fetchRow(
            string $query,
            int    $fetch_style = null,
                   $default     = null
        ) {
            $this->exec($query);
            try {
                $res = $this->stmt->fetch($fetch_style);
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    (
                        'Failed to fetch data from the database. ' .
                        $e->getMessage() . '. ' .
                        'Query: \'' . trim($query) . '\''
                    ),
                    4
                );
            }
            $this->stmt->closeCursor();
            return \Ada\Core\Type::set(
                $res === false ? $default : $res,
                'auto',
                true
            );
        }

        public function fetchRows(
            string $query,
            int    $fetch_style = null,
            string $key         = '',
            array  $default     = []
        ): array {
            $this->exec($query);
            try {
                $res = $this->stmt->fetchAll($fetch_style);
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    (
                        'Failed to fetch data from the database. ' .
                        $e->getMessage() . '. ' .
                        'Query: \'' . trim($query) . '\''
                    ),
                    4
                );
            }
            $this->stmt->closeCursor();
            $res = \Ada\Core\Type::set(
                $res === false ? $default : $res,
                'auto',
                true
            );
            if ($key === '') {
                return $res;
            }
            if (!key_exists($key, (array) reset($res))) {
                throw new \Ada\Core\Exception(
                    'Unknown key \'' . $key . '\'. Query: \'' . trim($query) . '\'',
                    5
                );
            }
            return array_combine(
                array_column((array) $res, $key),
                array_values($res)
            );
        }

        public function getAffectedRowsCount(): int {
            return (int) ($this->stmt ? $this->stmt->rowCount() : 0);
        }

        public function getAttribute(int $name) {
            if (!$this->isConnected()) {
                $this->connect();
            }
            try {
                return \Ada\Core\Type::set($this->pdo->getAttribute($name));
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    'Failed to get PDO attribute ' . $name . '. ' . $e->getMessage(),
                    10
                );
            }
        }

        public function getAttributes(): array {
            return $this->attributes;
        }

        public function getCharset(): string {
            return $this->charset;
        }

        public function getCollation(): string {
            return
                $this->collation === null
                    ? $this->collation = $this->detectCollation()
                    : $this->collation;
        }

        public function getColumnsCount(): int {
            return (int) ($this->stmt ? $this->stmt->columnCount() : 0);
        }

        public function getDateFormat(): string {
            return $this->date_format;
        }

        public function getDriver(): string {
            return $this->driver;
        }

        public function getDsnLine(bool $filled = true): string {
            $res = $this->dsn_format;
            if (!$filled) {
                return $res;
            }
            foreach (get_object_vars($this) as $k => $v) {
                $search = '%' . $k . '%';
                if (stripos($res, $search) !== false) {
                    $res = str_replace($search, $v, $res);
                }
            }
            return $res;
        }

        public function getHost(): string {
            return $this->host;
        }

        public function getMinVersion(): string {
            return $this->min_version;
        }

        public function getName(): string {
            return $this->name;
        }

        public function getNameSpace(): string {
            return __NAMESPACE__ . '\Drivers\\' . $this->getDriver();
        }

        public function getPassword(): string {
            return $this->password;
        }

        public function getPrefix(): string {
            return $this->prefix;
        }

        public function getQuote(): string {
            return $this->quote;
        }

        public function getTable(string $name): \Ada\Core\Db\Table {
            $class = $this->getNameSpace() . '\Table';
            return $class::init($name, $this);
        }

        public function getUser(): string {
            return $this->user;
        }

        public function getVersion(): string {
            return (string) $this->getAttribute(\PDO::ATTR_SERVER_VERSION);
        }

        public function insert(string $table, array $data): bool {
            return $this->exec(
                'INSERT INTO ' .
                $this->t($table) .
                $this->sqlSet($data)
            );
        }

        public function isConnected(): bool {
            return $this->pdo instanceof \PDO;
        }

        public function isTransactionOpen(): bool {
            if (!$this->isConnected()) {
                return false;
            }
            return (bool) $this->pdo->inTransaction();
        }

        public function lastErrorCode(): string {
            if (!$this->isConnected()) {
                return '';
            }
            return (string) $this->pdo->errorCode();
        }

        public function lastErrorInfo(): array {
            if (!$this->isConnected()) {
                return [];
            }
            return (array) $this->pdo->errorInfo();
        }

        public function lastInsertId() {
            return \Ada\Core\Type::set(
                $this->isConnected() ? $this->pdo->lastInsertId() : 0
            );
        }

        public function openTransaction(): bool {
            if (!$this->isConnected()) {
                $this->connect();
            }
            try {
                return (bool) $this->pdo->beginTransaction();
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    'Failed to start a transaction. ' . $e->getMessage(),
                    7
                );
            }
        }

        public function q(string $name, string $as = ''): string {
            return (
                (
                    strpos($name, '.') === false
                        ? ($this->getQuote() . $name . $this->getQuote())
                        : implode(
                            '.',
                            array_map(
                                function($el) {
                                    return (
                                        $this->getQuote() .
                                        $el .
                                        $this->getQuote()
                                    );
                                },
                                explode('.', $name)
                            )
                        )
                ) .
                (
                    $as
                        ? (' AS ' . $this->getQuote() . $as . $this->getQuote())
                        : ''
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

        public function rollBackTransaction(): bool {
            if (!$this->isTransactionOpen()) {
                return false;
            }
            try {
                return (bool) $this->pdo->rollBack();
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    'Failed to roll back a transaction. ' . $e->getMessage(),
                    9
                );
            }
        }

        public function setFetchMode(int $mode) {
            $this->fetch_mode = func_get_args();
        }

        public function sqlIn(array $array): string {
            $res = array_map(
                function($el) {
                    return $this->esc($el);
                },
                $array
            );
            return $res ? (' IN(' . implode(', ', $res) . ') ') : '';
        }

        public function sqlSet(array $array): string {
            $res = [];
            foreach ($array as $k => $v) {
                $res[] = (
                    $this->q($k) .
                    ' = ' .
                    $this->esc(\Ada\Core\Type::set($v, 'string'))
                );
            }
            return $res ? (' SET ' . implode(', ', $res) . ' ') : '';
        }

        public function t(string $table, string $as = ''): string {
            return $this->q($this->getPrefix() . $table, $as);
        }

        public function update(
            string $table,
            array  $data,
            string $condition
        ): bool {
            return $this->exec(
                'UPDATE ' .
                $this->t($table) .
                $this->sqlSet($data) .
                'WHERE ' . $condition
            );
        }


        protected function detectCollation(): string {
            return $this->fetchCell('
                SELECT ' . $this->q('DEFAULT_COLLATION_NAME') . '
                FROM '   . $this->q('INFORMATION_SCHEMA.SCHEMATA') . '
                WHERE '  . $this->q('SCHEMA_NAME') . '
                LIKE ' . $this->esc($this->getName()) . '
            ');
        }

    }