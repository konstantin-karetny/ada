<?php
    /**
    * @package   project/core
    * @version   1.0.0 04.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Db;

    abstract class Driver extends \Ada\Core\Proto {

        const
            ESC_TAG       = ':',
            WHITE_PARAMS  = [
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
                array_flip(static::WHITE_PARAMS)
            ) as $k => $v) {
                $this->$k = \Ada\Core\Type::set(
                    $v,
                    \Ada\Core\Type::get($this->$k)
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
                    (
                        'Version of the driver ' . $this->getVersion() .
                        ' less than required '   . $this->getMinVersion()
                    ),
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
                    4
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
                    13
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
                    2
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
                    2
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

        public function deleteRow(string $table_name, string $condition): bool {
            $query = $this->getQueryDeleteRow($table_name, $condition);
            try {
                return $this->exec($query);
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    (
                        'Failed to delete row. Query: \'' . trim($query) . '\'. ' .
                        $e->getMessage()
                    ),
                    12
                );
            }
        }

        public function disconnect(): bool {
            $this->pdo  = null;
            $this->stmt = null;
            return !$this->isConnected();
        }

        public function e(string $val) {
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
                    5
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
            $row = $this->fetchRow($query, \PDO::FETCH_NUM, []);
            $res = reset($row);
            return \Ada\Core\Type::set(
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
            $this->exec($query);
            $res = $this->fetchRows($query, \PDO::FETCH_ASSOC, $key);
            if (!$res) {
                return $default;
            }
            $column = $column === '' ? array_keys(reset($res))[0] : $column;
            if (!key_exists($column, reset($res))) {
                throw new \Ada\Core\Exception(
                    (
                        'Unknown column \'' . $column      . '\'. ' .
                        'Query: \''         . trim($query) . '\''
                    ),
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
                    (
                        'Failed to fetch data from the database. ' .
                        $e->getMessage() . '. ' .
                        'Query: \'' . trim($query) . '\''
                    ),
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
            return \Ada\Core\Type::set(
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
                    (
                        'Failed to fetch data from the database. ' .
                        $e->getMessage() . '. ' .
                        'Query: \'' . trim($query) . '\''
                    ),
                    10
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
                return \Ada\Core\Type::set($this->pdo->getAttribute($name));
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    'Failed to get PDO attribute ' . $name . '. ' . $e->getMessage(),
                    3
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
                    $this->fetchColumn($this->getQueryTablesNames())
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

        public function insertRow(string $table_name, array $row): bool {
            $query = $this->getQueryInsertRow($table_name, $row);
            try {
                return $this->exec($query);
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    (
                        'Failed to insert row. Query: \'' . trim($query) . '\'. ' .
                        $e->getMessage()
                    ),
                    6
                );
            }
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
                    'Failed to roll back transaction. ' . $e->getMessage(),
                    14
                );
            }
        }

        public function setFetchMode(int $mode) {
            $this->fetch_mode = func_get_args();
        }

        public function sqlIn(array $array): string {
            $res = array_map(
                function($el) {
                    return $this->e($el);
                },
                $array
            );
            return $res ? (' IN(' . implode(', ', $res) . ') ') : '';
        }

        public function t(string $table_name, string $as = ''): string {
            $dot = strpos($table_name, '.');
            return $this->q(
                $dot === false
                    ? ($this->getPrefix() . $table_name)
                    : (
                        substr($table_name, 0, $dot + 1) .
                        $this->getPrefix() .
                        substr($table_name,    $dot + 1)
                    ),
                $as
            );
        }

        public function updateRow(
            string $table_name,
            array  $row,
            string $condition
        ): bool {
            $query = $this->getQueryUpdateRow($table_name, $row, $condition);
            try {
                return $this->exec($query);
            } catch (\Throwable $e) {
                throw new \Ada\Core\Exception(
                    (
                        'Failed to update row. Query: \'' . trim($query) . '\'. ' .
                        $e->getMessage()
                    ),
                    7
                );
            }
        }

        protected function extractParams(): array {
            return array_map(
                'trim',
                array_merge(
                    $this->fetchRow('
                        SELECT ' .
                            $this->q('DEFAULT_CHARACTER_SET_NAME', 'charset')   . ', ' .
                            $this->q('DEFAULT_COLLATION_NAME',     'collation') . ', ' .
                            $this->q('SCHEMA_NAME',                'schema')    . '
                        FROM '   . $this->q('INFORMATION_SCHEMA.SCHEMATA')      . '
                        WHERE '  . $this->q('SCHEMA_NAME')                      . '
                        LIKE '   . $this->e($this->getName())                   . '
                    '),
                    [
                        'version' => $this->getAttribute(\PDO::ATTR_SERVER_VERSION)
                    ]
                )
            );
        }

        protected function getQueryDeleteRow(
            string $table_name,
            string $condition
        ): string {
            return 'DELETE FROM ' . $this->t($table_name) . ' WHERE ' . $condition;
        }

        protected function getQueryInsertRow(
            string $table_name,
            array  $row
        ): string {
            return '
                INSERT INTO ' .
                $this->t($table_name) . '
                (' .
                    implode(
                        ', ',
                        array_map([$this, 'q'], array_keys($row))
                    ) . '
                )
                VALUES(' .
                    implode(
                        ', ',
                        array_map([$this, 'e'], $row)
                    ) . '
                )
            ';
        }

        protected function getQueryTablesNames(): string {
            return '
                SELECT ' . $this->q('TABLE_NAME') . '
                FROM '   . $this->q('information_schema.TABLES') . '
                WHERE '  . $this->q('TABLE_SCHEMA') . ' LIKE ' . $this->e($this->getName())
            ;
        }

        protected function getQueryUpdateRow(
            string $table_name,
            array  $row,
            string $condition
        ): string {
            $res = 'UPDATE ' . $this->t($table_name) . ' SET ';
            foreach ($row as $k => $v) {
                $res .= $this->q($k) . ' = ' . $this->e($v) . ',';
            }
            return rtrim($res, " \t\n\r\0\x0B,") . ' WHERE ' . $condition;
        }

    }
