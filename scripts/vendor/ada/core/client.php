<?php
    /**
    * @package   ada/core
    * @version   1.0.0 07.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Client extends Singleton {

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

        public static function init(string $id = '', bool $cached = true): self {
            return parent::init($id, $cached);
        }

        protected function __construct(string $id) {
            if (!$id) {
                foreach ([
                    'auth',
                    'browser',
                    'cache_control',
                    'charset',
                    'content_type',
                    'encoding',
                    'ip',
                    'lang'
                ] as $prop) {
                    $method = ucfirst(Strings::toCamelCase($prop));
                    $this->{'set' . $method}(
                        $this->{'detect' . $method}()
                    );
                }
            }
        }

        public function detectAuth(): string {
            return Server::getFrstExisting(
                [
                    'HTTP_AUTHORIZATION',
                    'REDIRECT_HTTP_AUTHORIZATION'
                ],
                'string',
                $this->auth
            );
        }

        public function detectBrowser(): string {
            return Server::getString('HTTP_USER_AGENT', $this->browser);
        }

        public function detectCacheControl(): string {
            return Server::getString('HTTP_CACHE_CONTROL', $this->cache_control);
        }

        public function detectCharset(): string {
            return Server::getString('HTTP_ACCEPT_CHARSET', $this->charset);
        }

        public function detectContentType(): string {
            return Server::getString('HTTP_ACCEPT', $this->content_type);
        }

        public function detectEncoding(): string {
            return Server::getString('HTTP_ACCEPT_ENCODING', $this->encoding);
        }

        public function detectIp(bool $proxy = false): string {
            if (!$proxy) {
                return Server::getString('REMOTE_ADDR');
            }
            return Server::getFrstExisting(
                [
                    'HTTP_CLIENT_IP',
                    'HTTP_X_FORWARDED_FOR'
                ]
            );
        }

        public function detectLang(): string {
            return strtolower(
                substr(
                    Server::getString('HTTP_ACCEPT_LANGUAGE', $this->lang),
                    0,
                    2
                )
            );
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
                $line .= $this->{'get' . ucfirst(Strings::toCamelCase($prop))}();
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

    }
