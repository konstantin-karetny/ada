<?php
    /**
    * @package   project/core
    * @version   1.0.0 06.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Driver extends \Ada\Core\Proto {

        const
            ESC_TAG       = ':',
            PREFIX_ALIAS  = '#__',
            INIT_PARAMS   = [
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
            $attributes   = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ],
            $charset      = 'utf8',
            $collation    = 'utf8mb4_unicode_ci',
            $date_format  = 'Y-m-d H:i:s',
            $driver       = \Ada\Core\Db::DEFAULT_DRIVER,
            $dsn_format   = '',
            $fetch_mode   = [],
            $host         = 'localhost',
            $min_version  = '',
            $name         = '',
            $password     = '',
            /** @var \PDO */
            $pdo          = null,
            $port         = 0,
            $prefix       = '',
            $quote        = '`',
            $schema       = '',
            /** @var \PDOStatement */
            $stmt         = null,
            $tables_names = null,
            $user         = '',
            $version      = '';

        public static function init(array $params): \Ada\Core\Db\Driver {
            return new static($params);
        }

        protected function __construct(array $params) {
            foreach (array_intersect_key(
                $params,
                array_flip(static::INIT_PARAMS)
            ) as $k => $v) {
                $this->$k = \Ada\Core\Types::set(
                    $v,
                    \Ada\Core\Types::get($this->$k)
                );
            }
            $this->setProps($this->extractParams());
            if (
                version_compare(
                    $this->getVersion(),
                    $this->getMinVersion(),
                    '<'
                )
            ) {
                throw new \Ada\Core\Exception(
                    '
                        Version of the driver ' . $this->getVersion()    . '
                        less than required '    . $this->getMinVersion() . '
                    ',
                    1
                );
            }
        }

        public function beginTransaction(): bool {
            if (!$this->isConnected()) {
                $this->connect();
            }
            try {
                return (bool) $this->pdo->beginTransaction();
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    'Failed to begin transaction. ' . $e->getMessage(),
                    2
                );
            }
        }

        public function commitTransaction(): bool {
            if (!$this->isTransactionOpen()) {
                return false;
            }
            try {
                return (bool) $this->pdo->commit();
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    'Failed to close transaction. ' . $e->getMessage(),
                    3
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
                    4
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
                    5
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

        public function disconnect(): bool {
            $this->pdo  = null;
            $this->stmt = null;
            return !$this->isConnected();
        }

        public function e(string $value) {
            if ($value === '') {
                throw new \Ada\Core\Exception(
                    'Argument 1 passed to ' . __METHOD__ . '() must not be empty',
                    6
                );
            }
            if (is_numeric($value)) {
                return $value * 1;
            }
            return
                ' ' . static::ESC_TAG .
                str_replace(
                    static::ESC_TAG,
                    '\\' . static::ESC_TAG . '\\',
                    $value
                ) .
                static::ESC_TAG . ' ';
        }

        public function exec(string $query): bool {
            if (!$this->isConnected()) {
                $this->connect();
            }
            $pattern    = '/\s' . static::ESC_TAG . '(.+)' . static::ESC_TAG . '\s/U';
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
                            '/\\' . static::PREFIX_ALIAS . '/',
                            $pattern
                        ],
                        [
                            $this->getPrefix(),
                            ' ? '
                        ],
                        \Ada\Core\Type\Str::init($query)->oneLine(false)
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
                    '
                        Failed to execute a database query. ' . $e->getMessage() . '.
                        Query: \'' . \Ada\Core\Type\Str::init($query)->oneLine() . '\'
                    ',
                    7
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
            $res = \Ada\Core\Type\Arr::init(
                $this->fetchRow($query, \PDO::FETCH_NUM, [])
            )->first();
            return \Ada\Core\Types::set(
                $res === false ? $default : $res,
                $type
            );
        }

        public function fetchColumn(
            string $query,
            string $column  = '',
            string $key     = '',
            array  $default = []
        ): array {
            $res = $this->fetchRows($query, \PDO::FETCH_ASSOC, $key);
            if (!$res) {
                return $default;
            }
            $column = $column === '' ? array_keys(reset($res))[0] : $column;
            if (!key_exists($column, reset($res))) {
                throw new \Ada\Core\Exception(
                    '
                        Unknown column \'' . $column . '\'.
                        Query: \'' . \Ada\Core\Type\Str::init($query)->oneLine() . '\'
                    ',
                    8
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
                    '
                        Failed to fetch data from the database. ' . $e->getMessage() . '.
                        Query: \''     . \Ada\Core\Type\Str::init($query)->oneLine() . '\'
                    ',
                    9
                );
            }
            $this->stmt->closeCursor();
            switch (
                $fetch_style === null
                    ? $this->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE)
                    : $fetch_style
            ) {
                case \PDO::FETCH_ASSOC :
                case \PDO::FETCH_BOTH  :
                case \PDO::FETCH_NAMED :
                case \PDO::FETCH_NUM   :
                    $type = 'array';
                    break;
                default:
                    $type = 'auto';
            }
            return \Ada\Core\Types::set(
                $res === false ? $default : $res,
                $type,
                false
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
                    '
                        Failed to fetch data from the database. ' . $e->getMessage() . '.
                        Query: \''     . \Ada\Core\Type\Str::init($query)->oneLine() . '\'
                    ',
                    10
                );
            }
            $this->stmt->closeCursor();
            $res = \Ada\Core\Types::set(
                $res === false ? $default : $res,
                'auto',
                true
            );
            if ($key === '') {
                return $res;
            }
            if (!key_exists($key, (array) reset($res))) {
                throw new \Ada\Core\Exception(
                    '
                        Unknown key \'' . $key . '\'.
                        Query: \'' . \Ada\Core\Type\Str::init($query)->oneLine() . '\'
                    ',
                    11
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
                return \Ada\Core\Types::set($this->pdo->getAttribute($name));
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    '
                        Failed to get PDO attribute \'' . $name . '\'. ' .
                        $e->getMessage() . '
                    ',
                    12
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
            return $this->collation;
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
            return preg_replace('/[^\\\]+$/', '', get_class($this));
        }

        public function getPassword(): string {
            return $this->password;
        }

        public function getPort(): int {
            return $this->port;
        }

        public function getPrefix(): string {
            return $this->prefix;
        }

        public function getQuery(): \Ada\Core\Db\Query {
            $class = $this->getNameSpace() . 'Query';
            return $class::init($this);
        }

        public function getQuote(): string {
            return $this->quote;
        }

        public function getSchema(): string {
            return $this->schema;
        }

        public function getTable(
            string $name,
            bool   $cached = true
        ): \Ada\Core\Db\Table {
            $class = $this->getNameSpace() . 'Table';
            return $class::init($name, $this, $cached);
        }

        public function getTables(
            bool $as_objects = false,
            bool $cached     = true
        ): array {
            if (!$cached || $this->tables_names === null) {
                $this->tables_names = array_map(
                    function($el) {
                        return ltrim($el, $this->getPrefix());
                    },
                    $this->getQuery()
                        ->selectOne('TABLE_NAME')
                        ->from('information_schema.TABLES', '', false)
                        ->where('TABLE_SCHEMA', '=', $this->getName())
                        ->fetchColumn()
                );
            }
            if (!$as_objects) {
                return $this->tables_names;
            }
            $res = [];
            foreach ($this->tables_names as $name) {
                $res[$name] = $this->getTable($name, $cached);
            }
            return $res;
        }

        public function getUser(): string {
            return $this->user;
        }

        public function getVersion(): string {
            return $this->version;
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
            return \Ada\Core\Types::set(
                $this->isConnected() ? $this->pdo->lastInsertId() : 0
            );
        }

        public function q(string $name, string $alias = ''): string {
            $q   = $this->getQuote();
            $res = str_replace('.', $q . '.' . $q, $name);
            return
                (
                    preg_match('/\(.+\)/', $res)
                        ? str_replace(
                            [
                                '(',
                                ')'
                            ],
                            [
                                '(' . $q,
                                $q . ')'
                            ],
                            $res
                        )
                        : ($q . $res . $q)
                ) .
                (
                    $alias === ''
                        ? ''
                        : ' AS ' . $q . $alias . $q
                );
        }

        public function qs(array $names): string {
            return implode(
                ', ',
                array_map(
                    function($name, $alias) {
                        return is_int($name)
                            ? $this->q($alias)
                            : $this->q($name, $alias);
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
                    'Failed to roll back transaction. ' . $e->getMessage(),
                    13
                );
            }
        }

        public function setFetchMode(int $mode) {
            $this->fetch_mode = func_get_args();
        }

        public function t(string $table_name, string $alias = ''): string {
            $dot = strpos($table_name, '.');
            return $this->q(
                (
                    $dot === false
                        ? ($this->getPrefix() . $table_name)
                        : (
                            substr($table_name, 0, $dot + 1) .
                            $this->getPrefix() .
                            substr($table_name,    $dot + 1)
                        )
                ),
                $alias
            );
        }

        protected function extractParams(): array {
            return array_map(
                'trim',
                array_merge(
                    (
                        $this->getQuery()
                            ->select([
                                ['DEFAULT_CHARACTER_SET_NAME', 'charset'],
                                ['DEFAULT_COLLATION_NAME',     'collation'],
                                ['SCHEMA_NAME',                'schema']
                            ])
                            ->from('INFORMATION_SCHEMA.SCHEMATA', '', false)
                            ->where('SCHEMA_NAME', '=', $this->getName())
                            ->fetchRow()
                    ),
                    [
                        'version' => $this->getAttribute(\PDO::ATTR_SERVER_VERSION)
                    ]
                )
            );
        }

    }
