<?php
    /**
    * @package   ada/tools
    * @version   1.0.0 13.01.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Tools;

    class Url extends \Ada\Core\Singleton {

        protected const
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
            ];

        protected
            $initial  = '',
            $scheme   = '',
            $user     = '',
            $password = '',
            $host     = '',
            $port     = 0,
            $path     = '',
            $query    = '',
            $fragment = '',
            $vars     = [];

        public static function getInst(
            string $url    = '',
            array  $params = [],
            bool   $cached = true
        ): self {
            return parent::getInst(
                $url ? Clean::url($url) : self::current(),
                $params,
                $cached
            );
        }

        protected function __construct(string $url) {
            foreach ($this->parse($url) as $k => $v) {
                $this->$k = $v;
            }
            $this->initial = $url;
            $this->vars    = $this->parseQuery($this->query);
        }

        public static function current() {
            $res = 'http';
            if (
                (
                    !empty($_SERVER['HTTPS']) &&
                    strtolower(trim($_SERVER['HTTPS'])) !== 'off'
                ) ||
                (
                    !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                    strtolower(trim($_SERVER['HTTP_X_FORWARDED_PROTO'])) !== 'http'
                )
            ) {
                $res .= 's';
            }
            $res .= '://' . trim($_SERVER['HTTP_HOST']);
            if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI'])) {
                $res .= trim($_SERVER['REQUEST_URI']);
            }
            else {
                $res .= trim($_SERVER['SCRIPT_NAME']);
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $res .= '?' . trim($_SERVER['QUERY_STRING']);
                }
            }
            return Clean::url($res);
        }

        public function getSchemes(): array {
            return self::SCHEMES;
        }

        public function getParts(): array {
            return self::PARTS;
        }

        public function getDefaultParts(): array {
            return self::DEFAULT_PARTS;
        }

        public function getRootParts(): array {
            return self::ROOT_PARTS;
        }

        public function getRootDefaultParts(): array {
            return self::ROOT_DEFAULT_PARTS;
        }

        public function getInitial(): string {
            return $this->initial;
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

        public function getVar(
            string $key,
            string $type    = 'string',
            string $default = ''
        ) {
            return Type::set(
                key_exists($key, $this->vars) ? $this->vars[$key] : $default,
                $type
            );
        }

        public function getRoot(array $parts = self::ROOT_DEFAULT_PARTS): string {
            return $this->toString(
                array_intersect(
                    self::ROOT_PARTS,
                    $parts
                )
            );
        }

        public function setScheme(string $scheme): string {
            $scheme = strtolower(trim($scheme));
            if ($scheme == '') {
                throw new \Ada\Core\Exception('Scheme can not be empty');
            }
            if (!in_array($scheme, self::SCHEMES)) {
                throw new \Ada\Core\Exception('Unknown scheme \'' . $scheme . '\'');
            }
            return $this->scheme = $scheme;
        }

        public function setUser(string $user): string {
            return $this->user = strtolower(trim($user));
        }

        public function setPassword(string $password): string {
            return $this->password = strtolower(trim($password));
        }

        public function setHost(string $host): string {
            $host = strtolower(trim($host));
            if ($host == '') {
                throw new \Ada\Core\Exception('Host can not be empty');
            }
            return $this->host = $host;
        }

        public function setPort(int $port): int {
            return $this->port = $port;
        }

        public function setPath(string $path): string {
            return $this->path = strtolower(trim(trim($path), '/'));
        }

        public function setQuery(string $query): string {
            $this->query = strtolower(trim(trim($query), '/'));
            $this->vars  = $this->parseQuery($this->query);
            return $this->query;
        }

        public function setFragment(string $fragment): string {
            return $this->fragment = strtolower(trim(trim($fragment), '/'));
        }

        public function setVars(array $vars): array {
            foreach ($vars as $k => $v) {
                $this->setVar($k, $v);
            }
            return $this->vars;
        }

        public function setVar(string $key, string $value) {
            $this->vars[$key] = Type::typify($value);
            $this->query      = $this->buildQuery($this->vars);
            return $value;
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

        protected function parse(string $url): array {
            $res = array_merge(
                array_fill_keys(self::PARTS, ''),
                parse_url(urldecode($url))
            );
            foreach ($res as $k => $v) {
                $res[$k] = Type::set(
                    trim(trim($v, '/')),
                    gettype($this->$k)
                );
            }
            return $res;
        }

        protected function parseQuery(string $query): array {
            $res = [];
            parse_str($query, $res);
            return Type::typify($res);
        }

        protected function buildQuery(array $vars): string {
            return http_build_query($vars);
        }

    }
