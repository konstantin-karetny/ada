<?php
    /**
    * @package   ada/core
    * @version   1.0.0 17.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Client extends Proto {

        use Traits\Singleton;

        const
            SIGNATURE_PARTS = [
                'browser',
                'charset',
                'encoding',
                'lang'
            ];

        protected
            $auth          = '',
            $browser       = '',
            $cache_control = 'no-cache',
            $charset       = 'UTF-8',
            $content_type  = 'text/html',
            $encoding      = '',
            $ip            = '',
            $lang          = 'en';

        public static function init(bool $current = true): self {
            return
                $current
                    ? static::initSingleton('', true, ...func_get_args())
                    : new static($current);
        }

        protected function __construct(bool $current = true) {
            if (!$current) {
                return;
            }
            foreach ($this as $prop => $val) {
                $method   = Strings::toCamelCase($prop);
                $detector = 'detect' . $method;
                if (!method_exists($this, $detector)) {
                    continue;
                }
                switch ($prop) {
                    case 'ip':
                        $val = $this->$detector(false, $val);
                        break;
                    default:
                        $val = $this->$detector($val);
                }
                $this->{'set' . $method}($val);
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
                $line .= $this->{'get' . Strings::toCamelCase($prop)}();
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
