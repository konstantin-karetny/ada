<?php
    /**
    * @package   project/core
    * @version   1.0.0 29.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Client extends Proto {

        const
            SIGNATURE_PARTS = [
                'browser',
                'charset',
                'encoding',
                'lang'
            ];

        protected static
            $cache;

        protected
            $auth           = '',
            $browser        = '',
            $cache_control  = 'no-cache',
            $charset        = 'UTF-8',
            $content_type   = 'text/html',
            $encoding       = '',
            $ip             = '',
            $lang           = 'en';

        public static function init(bool $current = true): self {
            return new static($current);
        }

        public function __construct(bool $current = true) {
            if (!$current) {
                return;
            }
            if (!static::$cache) {
                foreach ($this as $prop => $v) {
                    $detector = 'detect' . Str::toCamelCase($prop);
                    if (!method_exists($this, $detector)) {
                        continue;
                    }
                    switch ($prop) {
                        case 'ip':
                            $v = $this->$detector(false, $v);
                            break;
                        default:
                            $v = $this->$detector($v);
                    }
                    static::$cache[$prop] = $v;
                }
            }
            foreach (static::$cache as $prop => $v) {
                $this->{'set' . Str::toCamelCase($prop)}($v);
            }
        }

        public function getAuth(): string {
            return $this->auth;
        }

        public function getBrowser(): string {
            return $this->browser;
        }

        public function getCacheControl(): string {
            return $this->cache_control;
        }

        public function getCharset(): string {
            return $this->charset;
        }

        public function getContentType(): string {
            return $this->content_type;
        }

        public function getEncoding(): string {
            return $this->encoding;
        }

        public function getIp(): string {
            return $this->ip;
        }

        public function getLang(): string {
            return $this->lang;
        }

        public function getSignature(array $parts = self::SIGNATURE_PARTS): string {
            $line = implode($parts);
            foreach ($parts as $prop) {
                $line .= $this->{'get' . Str::toCamelCase($prop)}();
            }
            return Hash::asMd5($line);
        }

        public function setAuth(string $auth) {
            $this->auth = $auth;
        }

        public function setBrowser(string $browser) {
            $this->browser = $browser;
        }

        public function setCacheControl(string $cache_control) {
            $this->cache_control = $cache_control;
        }

        public function setCharset(string $charset) {
            $this->charset = $charset;
        }

        public function setContentType(string $content_type) {
            $this->content_type = $content_type;
        }

        public function setEncoding(string $encoding) {
            $this->encoding = $encoding;
        }

        public function setIp(string $ip) {
            $this->ip = $ip;
        }

        public function setLang(string $lang) {
            $this->lang = $lang;
        }

        protected function detectAuth(string $default = ''): string {
            return Server::getFrstExisting(
                [
                    'HTTP_AUTHORIZATION',
                    'REDIRECT_HTTP_AUTHORIZATION'
                ],
                'string',
                $default
            );
        }

        protected function detectBrowser(string $default = ''): string {
            return Server::getString('HTTP_USER_AGENT', $default);
        }

        protected function detectCacheControl(string $default = ''): string {
            return Server::getString('HTTP_CACHE_CONTROL', $default);
        }

        protected function detectCharset(string $default = ''): string {
            return Server::getString('HTTP_ACCEPT_CHARSET', $default);
        }

        protected function detectContentType(string $default = ''): string {
            return Server::getString('HTTP_ACCEPT', $default);
        }

        protected function detectEncoding(string $default = ''): string {
            return Server::getString('HTTP_ACCEPT_ENCODING', $default);
        }

        protected function detectIp(
            bool   $proxy   = false,
            string $default = ''
        ): string {
            if (!$proxy) {
                return Server::getString('REMOTE_ADDR');
            }
            return Server::getFrstExisting(
                [
                    'HTTP_CLIENT_IP',
                    'HTTP_X_FORWARDED_FOR'
                ],
                'string',
                $default
            );
        }

        protected function detectLang(string $default = ''): string {
            return strtolower(
                substr(
                    Server::getString('HTTP_ACCEPT_LANGUAGE', $default),
                    0,
                    2
                )
            );
        }

    }
