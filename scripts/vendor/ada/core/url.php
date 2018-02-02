<?php
    /**
    * @package   ada/core
    * @version   1.0.0 01.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Url extends Singleton {

        const
            SCHEMES = [
                'http',
                'https'
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
            DEFAULT_PARTS = [
                'scheme',
                'host',
                'path',
                'query',
                'fragment'
            ],
            ROOT_PARTS = [
                'scheme',
                'user',
                'password',
                'host',
                'port'
            ],
            ROOT_DEFAULT_PARTS = [
                'scheme',
                'host'
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

        protected
            $scheme   = '',
            $user     = '',
            $password = '',
            $host     = '',
            $port     = 80,
            $path     = '',
            $query    = '',
            $fragment = '',
            $vars     = [];

        public static function init(
            string $url    = '',
            bool   $cached = true
        ): self {
            return parent::init($url ? $url : self::current(), $cached);
        }

        protected function __construct(string $url) {
            if (!self::check($url)) {
                throw new Exception('Wrong url \'' . $url . '\'', 1);
            }
            foreach ($this->parse(self::clean($url)) as $k => $v) {
                $this->{'set' . ucfirst($k)}($v);
            }
        }

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
                    array_keys(self::UNSAFE_CHARS_CODES),
                    array_values(self::UNSAFE_CHARS_CODES),
                    self::encode(
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
            return self::decode($res);
        }

        public static function current() {
            $res = 'http';
            if (
                (
                    !empty($_SERVER['HTTPS']) &&
                    self::clean($_SERVER['HTTPS']) !== 'off'
                ) ||
                (
                    !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                    self::clean($_SERVER['HTTP_X_FORWARDED_PROTO']) !== 'http'
                )
            ) {
                $res .= 's';
            }
            $res .= '://' . self::clean($_SERVER['HTTP_HOST']);
            if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI'])) {
                $res .= self::clean($_SERVER['REQUEST_URI']);
            }
            else {
                $res .= self::clean($_SERVER['SCRIPT_NAME']);
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $res .= '?' . self::clean($_SERVER['QUERY_STRING']);
                }
            }
            return self::clean($res);
        }

        public static function isInternal(string $url): bool {
            return self::init($url)->getRoot() == self::init()->getRoot();
        }

        public static function redirect(
            string  $url     = '',
            bool    $replace = true,
            int     $status  = 301,
            bool    $cache   = false
        ) {

            //C:\OSPanel\domains\ada-pre\core\classes\uri.php
            //C:\OSPanel\domains\joomla\libraries\legacy\application\application.php
            //C:\OSPanel\domains\ada\trunk\_docs\ada.txt
            //C:\OSPanel\domains\ada\trunk\_docs\core\url.txt

            if (headers_sent()) {
                echo '<script>document.location.href="' . str_replace('"', '&apos;', $url) . '";</script>';
                return;
            }


            if($uri === '') $uri = self::root();
            if(!$caching) {
                header("Cache-Control: no-cache, must-revalidate");
                header("Expires: Wed, 13 Dec 1989 04:00:00 GMT");
            }
            header('Location: ' . $uri, $replace, $http_response_code);
            exit;
        }

        public function delVar(string $key): bool {
            if (key_exists($key, $this->vars)) {
                unset($this->vars[$key]);
                $this->query = $this->buildQuery($this->vars);
                return true;
            }
            return false;
        }

        public function isSSL(): bool {
            return $this->scheme == 'https';
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

        public function getScheme(): string {
            return $this->scheme;
        }

        public function getUser(): string {
            return $this->user;
        }

        public function getPassword(): string {
            return $this->password;
        }

        public function getHost(): string {
            return $this->host;
        }

        public function getPort(): int {
            return $this->port;
        }

        public function getPath(): string {
            return $this->path;
        }

        public function getQuery(): string {
            return $this->query;
        }

        public function getFragment(): string {
            return $this->fragment;
        }

        public function getVars(): array {
            return $this->vars;
        }

        public function getVar(string $key, string $default = ' '): string {
            return key_exists($key, $this->vars) ? $this->vars[$key] : $default;
        }

        public function getRoot(array $parts = self::ROOT_DEFAULT_PARTS): string {
            return $this->toString(
                array_intersect(self::ROOT_PARTS, $parts)
            );
        }

        public function setScheme(string $scheme) {
            $scheme = self::clean($scheme);
            if ($scheme == '') {
                throw new Exception('Scheme can not be empty', 3);
            }
            if (!in_array($scheme, self::SCHEMES)) {
                throw new Exception('Unknown scheme \'' . $scheme . '\'', 4);
            }
            $this->scheme = $scheme;
        }

        public function setUser(string $user) {
            $this->user = self::clean($user);
        }

        public function setPassword(string $password) {
            $this->password = self::clean($password);
        }

        public function setHost(string $host) {
            $host = self::clean($host);
            if ($host == '') {
                throw new Exception('Host can not be empty', 5);
            }
            $this->host = $host;
        }

        public function setPort(int $port) {
            $this->port = $port;
        }

        public function setPath(string $path) {
            $this->path = self::clean($path);
        }

        public function setQuery(string $query) {
            $this->setVars($this->parseQuery($query));
        }

        public function setFragment(string $fragment) {
            $this->fragment = self::clean($fragment);
        }

        public function setVars(array $vars) {
            foreach ($vars as $k => $v) {
                $this->addVar($k, $v);
            }
        }

        public function addVar(string $key, string $value) {
            $this->vars[$key] = self::clean($value);
            $this->query      = $this->buildQuery($this->vars);
        }

        public function setRoot(string $root) {
            $root_obj = self::init($root);
            foreach (self::ROOT_PARTS as $part) {
                $this->{'set' . ucfirst($part)}(
                    $root_obj->{'get' . ucfirst($part)}()
                );
            }
        }

        protected static function encode(string $url): string {
            return str_replace(
                array_values(self::SPECIAL_CHARS_CODES),
                array_keys(self::SPECIAL_CHARS_CODES),
                urlencode($url)
            );
        }

        protected static function decode(string $url): string {
            return urldecode(
                str_replace(
                    array_keys(self::SPECIAL_CHARS_CODES),
                    array_values(self::SPECIAL_CHARS_CODES),
                    $url
                )
            );
        }

        protected function parse(string $url): array {
            $res = [];
            foreach ((array) parse_url(self::clean($url)) as $k => $v) {
                if (!in_array($k, self::PARTS)) {
                    continue;
                }
                $res[$k] = Type::set(self::clean($v), Type::get($this->$k));
            }
            return $res;
        }

        protected function parseQuery(string $query): array {
            $res = [];
            parse_str($query, $res);
            return $res;
        }

        protected function buildQuery(array $vars): string {
            return http_build_query($vars);
        }

    }
