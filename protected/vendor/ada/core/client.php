<?php
    /**
    * @package   project/core
    * @version   1.0.0 30.03.2018
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
            $cache          = [];

        protected
            $auth           = '',
            $browser        = '',
            $cache_control  = 'no-cache',
            $charset        = 'UTF-8',
            $content_type   = 'text/html',
            $encoding       = '',
            $ip             = '',
            $ip_proxy       = '',
            $lang           = 'en';

        public static function init(bool $current = true): self {
            return new static($current);
        }

        public function __construct(bool $current = true) {
            if ($current) {
                $this->setProps($this->fetchServerData());
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

        public function getIpProxy(): string {
            return $this->ip_proxy;
        }

        public function getLang(): string {
            return $this->lang;
        }

        public function getSignature(
            array $parts = self::SIGNATURE_PARTS
        ): string {
            $res = '';
            foreach ($parts as $part) {
                $res .= $part . $this->{'get' . Str::toCamelCase($part)}();
            }
            return Str::hash($res);
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

        public function setIpProxy(string $ip_proxy) {
            $this->ip_proxy = $ip_proxy;
        }

        public function setLang(string $lang) {
            $this->lang = $lang;
        }

        protected function fetchServerData(bool $cached = true): array {
            return
                $cached && static::$cache
                    ? static::$cache
                    : static::$cache = [
                        'auth'          => Server::getFrstExisting(
                            [
                                'HTTP_AUTHORIZATION',
                                'REDIRECT_HTTP_AUTHORIZATION'
                            ],
                            'string',
                            $this->getAuth()
                        ),
                        'browser'       => Server::getString(
                            'HTTP_USER_AGENT',
                            $this->getBrowser()
                        ),
                        'cache_control' => Server::getString(
                            'HTTP_CACHE_CONTROL',
                            $this->getCacheControl()
                        ),
                        'charset'       => Server::getString(
                            'HTTP_ACCEPT_CHARSET',
                            $this->getCharset()
                        ),
                        'content_type'  => Server::getString(
                            'HTTP_ACCEPT',
                            $this->getContentType()
                        ),
                        'encoding'      => Server::getString(
                            'HTTP_ACCEPT_ENCODING',
                            $this->getEncoding()
                        ),
                        'ip'            => Server::getString(
                            'REMOTE_ADDR',
                            $this->getIp()
                        ),
                        'ip_proxy'      => Server::getFrstExisting(
                            [
                                'HTTP_CLIENT_IP',
                                'HTTP_X_FORWARDED_FOR'
                            ],
                            'string',
                            $this->getIpProxy()
                        ),
                        'lang'          => strtolower(
                            substr(
                                Server::getString(
                                    'HTTP_ACCEPT_LANGUAGE',
                                    $this->getLang()
                                ),
                                0,
                                2
                            )
                        )
                    ];
        }

    }
