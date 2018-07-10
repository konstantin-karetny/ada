<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Url extends Proto {

        const
            DEFAULT_PARTS       = [
                'scheme',
                'host',
                'path',
                'query',
                'fragment'
            ],
            PARTS               = [
                'scheme',
                'user',
                'password',
                'host',
                'port',
                'path',
                'query',
                'fragment'
            ],
            ROOT_DEFAULT_PARTS  = [
                'scheme',
                'host'
            ],
            ROOT_PARTS          = [
                'scheme',
                'user',
                'password',
                'host',
                'port'
            ],
            SCHEMES             = [
                'http',
                'https'
            ],
            SPECIAL_CHARS_CODES = [
                '!'  => '%21',
                '#'  => '%23',
                '$'  => '%24',
                '&'  => '%26',
                '\'' => '%27',
                '('  => '%28',
                ')'  => '%29',
                '*'  => '%2A',
                ','  => '%2C',
                '/'  => '%2F',
                ':'  => '%3A',
                ';'  => '%3B',
                '='  => '%3D',
                '?'  => '%3F',
                '@'  => '%40',
                '['  => '%5B',
                ']'  => '%5D'
            ],
            UNSAFE_CHARS_CODES  = [
                '\'' => '%27',
                '"'  => '%22',
                '<'  => '%3C',
                '>'  => '%3E'
            ];

        protected static
            $cache              = [],
            $default_root       = '',
            $inited             = false;

        protected
            $fragment           = '',
            $host               = '',
            $password           = '',
            $path               = '',
            $port               = 80,
            $query              = '',
            $scheme             = '',
            $user               = '',
            $vars               = [];

        public static function check(string $url, $options = null): bool {
            if (filter_var($url, FILTER_VALIDATE_URL, $options)) {
                return true;
            }
            $mb_strlen = mb_strlen($url);
            if ($mb_strlen == strlen($url)) {
                return false;
            }
            $url_ascii = str_repeat(' ', $mb_strlen);
            for ($i = 0; $i < $mb_strlen; $i++) {
                $char          = mb_substr($url, $i, 1);
                $url_ascii[$i] = strlen($char) != mb_strlen($char) ? 'a' : $char;
            }
            return (bool) filter_var($url_ascii, FILTER_VALIDATE_URL, $options);
        }

        public static function clean(string $url): string {
            $res = filter_var(
                str_replace(
                    array_keys(static::UNSAFE_CHARS_CODES),
                    array_values(static::UNSAFE_CHARS_CODES),
                    static::encode(
                        strtolower(
                            trim($url, Type\Str::TRIM_CHARS . '/')
                        )
                    )
                ),
                FILTER_SANITIZE_URL
            );
            if ($res === false) {
                throw new Exception('Failed to clean url \'' . $url . '\'', 1);
            }
            return static::decode($res);
        }

        public static function getDefaultRoot(): string {
            return static::$default_root;
        }

        public static function init(string $url = ''): \Ada\Core\Url {
            return new static($url);
        }

        public static function isInited(): bool {
            return static::$inited;
        }

        public static function preset(array $params): bool {
            if (static::$inited) {
                return false;
            }
            foreach ($params as $k => $v) {
                switch (Clean::cmd($k)) {
                    case 'default_root':
                        static::$default_root = static::init($v)->getRoot();
                        break;
                }
            }
            static::$cache = [];
            return true;
        }

        protected static function decode(string $url): string {
            return urldecode(
                str_replace(
                    array_keys(static::SPECIAL_CHARS_CODES),
                    array_values(static::SPECIAL_CHARS_CODES),
                    $url
                )
            );
        }

        protected static function encode(string $url): string {
            return str_replace(
                array_values(static::SPECIAL_CHARS_CODES),
                array_keys(static::SPECIAL_CHARS_CODES),
                urlencode($url)
            );
        }

        protected function __construct(string $url = '') {
            $url = $url === '' ? $this->detectCurrent() : $url;
            if (!static::check($url)) {
                throw new Exception('Wrong url \'' . $url . '\'', 2);
            }
            foreach ($this->parse(static::clean($url)) as $k => $v) {
                $this->{'set' . ucfirst($k)}($v);
            }
            static::$inited = true;
        }

        public function dropVar(string $name): bool {
            $name = Clean::cmd($name);
            if (isset($this->vars[$name])) {
                unset($this->vars[$name]);
                $this->query = $this->buildQuery($this->vars);
                return true;
            }
            return false;
        }

        public function getFragment(): string {
            return $this->fragment;
        }

        public function getHost(): string {
            return $this->host;
        }

        public function getPassword(): string {
            return $this->password;
        }

        public function getPath(): string {
            return $this->path;
        }

        public function getPort(): int {
            return $this->port;
        }

        public function getQuery(): string {
            return $this->query;
        }

        public function getRoot(array $parts = self::ROOT_DEFAULT_PARTS): string {
            return $this->toStr(
                array_intersect(static::ROOT_PARTS, $parts)
            );
        }

        public function getScheme(): string {
            return $this->scheme;
        }

        public function getUser(): string {
            return $this->user;
        }

        public function getVar(
            string $name,
            string $filter,
                   $default = ''
        ) {
            return Clean::value(
                $this->vars[Clean::cmd($name)] ?? $default,
                $filter
            );
        }

        public function getVars(string $filter = ''): array {
            return $filter ? Clean::values($this->vars, $filter) : $this->vars;
        }

        public function isInternal(): bool {
            return $this->getRoot() == static::init()->getRoot();
        }

        public function isSSL(): bool {
            return $this->scheme == 'https';
        }

        public function redirect(
            int  $delay              = 0,
            bool $replace            = true,
            int  $http_response_code = 302
        ) {
            if (headers_sent()) {
                echo (
                    '<script>document.location.href="' .
                    str_replace('"', '&apos;', $this->toStr()) .
                    '";</script>'
                );
                return;
            }
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
            header(
                'Refresh: ' . ($delay > 0 ? $delay : 0) . '; ' . $this->toStr(),
                $replace,
                $http_response_code
            );
        }

        public function setFragment(string $fragment) {
            $this->fragment = static::clean($fragment);
        }

        public function setHost(string $host) {
            $host = static::clean($host);
            if ($host == '') {
                throw new Exception(
                    'Argument 1 passed to ' . __METHOD__ . '() must not be empty',
                    3
                );
            }
            $this->host = $host;
        }

        public function setPassword(string $password) {
            $this->password = static::clean($password);
        }

        public function setPath(string $path) {
            $this->path = static::clean($path);
        }

        public function setPort(int $port) {
            $this->port = $port;
        }

        public function setQuery(string $query) {
            $this->setVars($this->parseQuery($query));
        }

        public function setRoot(string $root) {
            $root_obj = static::init($root);
            foreach (static::ROOT_PARTS as $part) {
                $this->{'set' . ucfirst($part)}(
                    $root_obj->{'get' . ucfirst($part)}()
                );
            }
        }

        public function setScheme(string $scheme) {
            $scheme = static::clean($scheme);
            if ($scheme == '') {
                throw new Exception(
                    'Argument 1 passed to ' . __METHOD__ . '() must not be empty',
                    4
                );
            }
            if (!in_array($scheme, static::SCHEMES)) {
                throw new Exception('Unknown scheme \'' . $scheme . '\'', 5);
            }
            $this->scheme = $scheme;
        }

        public function setUser(string $user) {
            $this->user = static::clean($user);
        }

        public function setVar(string $name, string $value) {
            $this->vars[Clean::cmd($name)] = static::clean($value);
            $this->query                   = $this->buildQuery($this->vars);
        }

        public function setVars(array $vars) {
            foreach ($vars as $k => $v) {
                $this->setVar($k, $v);
            }
        }

        public function toStr(array $parts = self::DEFAULT_PARTS): string {
            $res = '';
            if (in_array('scheme', $parts)) {
                $res .= $this->scheme . '://';
            }
            if (in_array('user', $parts) && $this->user != '') {
                $res .= $this->user . ':';
                if (in_array('password', $parts)) {
                    $res .= $this->password;
                }
                $res .= '@';
            }
            if (in_array('host', $parts)) {
                $res .= $this->host;
            }
            if (in_array('port', $parts) && $this->port > 0) {
                $res .= ':' . $this->port;
            }
            if (in_array('path', $parts) && $this->path != '') {
                $res .= '/' . $this->path;
            }
            if (in_array('query', $parts) && $this->query != '') {
                $res .= '?' . $this->query;
            }
            if (in_array('fragment', $parts) && $this->fragment != '') {
                $res .= '#' . $this->fragment;
            }
            return $res;
        }

        protected function buildQuery(array $vars): string {
            return http_build_query($vars);
        }

        protected function detectCurrent(bool $cached = true): string {
            if ($cached && isset(static::$cache['current'])) {
                return static::$cache['current'];
            }
            $res = static::getDefaultRoot();
            if (!$res) {
                $res = 'http';
                if (
                    strtolower(trim(
                        Inp\Server::getStr('HTTPS', 'off')
                    )) !== 'off' ||
                    strtolower(trim(
                        Inp\Server::getStr('HTTP_X_FORWARDED_PROTO', 'http')
                    )) !== 'http'
                ) {
                    $res .= 's';
                }
                $res .= '://' . Inp\Server::getUrl('HTTP_HOST');
            }
            if (
                Inp\Server::getBool('PHP_SELF') &&
                Inp\Server::getBool('REQUEST_URI')
            ) {
                $res .= '/' . Inp\Server::getUrl('REQUEST_URI');
            }
            else {
                $res .= Inp\Server::getUrl('SCRIPT_NAME');
                if (Inp\Server::getBool('QUERY_STRING')) {
                    $res .= '?' . Inp\Server::getUrl('QUERY_STRING');
                }
            }
            return static::$cache['current'] = static::clean($res);
        }

        protected function parse(string $url): array {
            $res = [];
            foreach ((array) parse_url(static::clean($url)) as $k => $v) {
                if (!in_array($k, static::PARTS)) {
                    continue;
                }
                $res[$k] = Types::set(static::clean($v), Types::get($this->$k));
            }
            if ($res['host'] !== Inp\Server::getUrl('HTTP_HOST')) {
                return $res;
            }
            $script_path = Fs\File::init(
                Inp\Server::getPath(
                    (
                        strpos(php_sapi_name(), 'cgi') !== false &&
                        Inp\Server::getBool('REQUEST_URI') === ''  &&
                        !ini_get('cgi.fix_pathinfo')
                    )
                        ? 'PHP_SELF'
                        : 'SCRIPT_NAME'
                )
            )->getDir()->getPath();
            if ($script_path && strpos($res['path'] ?? '', $script_path) === 0) {
                $length       = strlen($script_path);
                $res['host'] .= '/' . substr($res['path'], 0, $length);
                $res['path']  = substr($res['path'], $length);
            }
            return $res;
        }

        protected function parseQuery(string $query): array {
            $res = [];
            parse_str($query, $res);
            return $res;
        }

    }
