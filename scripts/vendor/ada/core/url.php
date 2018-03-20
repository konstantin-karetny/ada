<?php
    /**
    * @package   ada/core
    * @version   1.0.0 20.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Url extends Proto {

        const
            DEFAULT_PARTS = [
                'scheme',
                'host',
                'path',
                'query',
                'fragment'
            ],
            PARTS = [
                'scheme',
                'user',
                'password',
                'host',
                'port',
                'path',
                'query',
                'fragment'
            ],
            ROOT_DEFAULT_PARTS = [
                'scheme',
                'host'
            ],
            ROOT_PARTS = [
                'scheme',
                'user',
                'password',
                'host',
                'port'
            ],
            SCHEMES = [
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
            UNSAFE_CHARS_CODES = [
                '\'' => '%27',
                '"'  => '%22',
                '<'  => '%3C',
                '>'  => '%3E'
            ];

        protected static
            $current      = '',
            $default_root = '',
            $inited       = '';

        protected
            $fragment     = '',
            $host         = '',
            $password     = '',
            $path         = '',
            $port         = 80,
            $query        = '',
            $scheme       = '',
            $user         = '',
            $vars         = [];

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
                            trim($url, " \t\n\r\0\x0B/")
                        )
                    )
                ),
                FILTER_SANITIZE_URL
            );
            if ($res === false) {
                throw new Exception('Failed to clean url \'' . $url . '\'', 2);
            }
            return static::decode($res);
        }

        public static function getDefaultRoot(): string {
            return static::$default_root;
        }

        public static function init(string $url = ''): self {
            return new static($url);
        }

        public static function setDefaultRoot(string $default_root): bool {
            if (static::$inited) {
                return false;
            }
            static::$default_root = static::init($default_root)->getRoot();
            static::$current      = '';
            static::$inited       = false;
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

        public function __construct(string $url = '') {
            if (static::$current === '') {
                static::$current = $this->detectCurrent();
                if (static::getDefaultRoot()) {
                    static::$current = static::init(static::$current);
                    static::$current->setRoot(static::getDefaultRoot());
                    static::$current = static::$current->toString();
                }
            }
            $url = $url === '' ? static::$current : $url;
            if (!static::check($url)) {
                throw new Exception('Wrong url \'' . $url . '\'', 1);
            }
            foreach ($this->parse(static::clean($url)) as $k => $v) {
                $this->{'set' . ucfirst($k)}($v);
            }
            static::$inited = true;
        }

        public function delVar(string $name): bool {
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
            return $this->toString(
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
                    str_replace('"', '&apos;', $this->toString()) .
                    '";</script>'
                );
                return;
            }
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
            header(
                'Refresh: ' . ($delay > 0 ? $delay : 0) . '; ' . $this->toString(),
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
                throw new Exception('Host can not be empty', 5);
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
                throw new Exception('Scheme can not be empty', 3);
            }
            if (!in_array($scheme, static::SCHEMES)) {
                throw new Exception('Unknown scheme \'' . $scheme . '\'', 4);
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

        public function toString(array $parts = self::DEFAULT_PARTS): string {
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

        protected function detectCurrent(): string {
            $res = 'http';
            if (
                strtolower(trim(
                    Server::getString('HTTPS', 'off')
                )) !== 'off' ||
                strtolower(trim(
                    Server::getString('HTTP_X_FORWARDED_PROTO', 'http')
                )) !== 'http'
            ) {
                $res .= 's';
            }
            $res .= '://' . Server::getUrl('HTTP_HOST');
            if (Server::getBool('PHP_SELF') && Server::getBool('REQUEST_URI')) {
                $res .= '/' . Server::getUrl('REQUEST_URI');
            }
            else {
                $res .= Server::getUrl('SCRIPT_NAME');
                if (Server::getBool('QUERY_STRING')) {
                    $res .= '?' . Server::getUrl('QUERY_STRING');
                }
            }
            return static::clean($res);
        }

        protected function parse(string $url): array {
            $res = [];
            foreach ((array) parse_url(static::clean($url)) as $k => $v) {
                if (!in_array($k, static::PARTS)) {
                    continue;
                }
                $res[$k] = Type::set(static::clean($v), Type::get($this->$k));
            }
            if ($res['host'] !== Server::getUrl('HTTP_HOST')) {
                return $res;
            }
            $script_path = File::init(
                Server::getPath(
                    (
                        strpos(php_sapi_name(), 'cgi') !== false &&
                        Server::getBool('REQUEST_URI') === ''  &&
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
