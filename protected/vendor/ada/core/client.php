<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    use Ada\Core\Inp\Server as Server;

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

        public static function init(bool $current = true): \Ada\Core\Client {
            return new static($current);
        }

        protected function __construct(bool $current = true) {
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
                $method = 'get' . Type\Str::init($part)->toCamelCase();
                $res   .= $part . $this->$method();
            }
            return Type\Str::init($res)->hash();
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
                        'auth'          => Server::getFirstExisting(
                            [
                                'HTTP_AUTHORIZATION',
                                'REDIRECT_HTTP_AUTHORIZATION'
                            ],
                            'str',
                            $this->getAuth()
                        ),
                        'browser'       => Server::getStr(
                            'HTTP_USER_AGENT',
                            $this->getBrowser()
                        ),
                        'cache_control' => Server::getStr(
                            'HTTP_CACHE_CONTROL',
                            $this->getCacheControl()
                        ),
                        'charset'       => Server::getStr(
                            'HTTP_ACCEPT_CHARSET',
                            $this->getCharset()
                        ),
                        'content_type'  => Server::getStr(
                            'HTTP_ACCEPT',
                            $this->getContentType()
                        ),
                        'encoding'      => Server::getStr(
                            'HTTP_ACCEPT_ENCODING',
                            $this->getEncoding()
                        ),
                        'ip'            => Server::getStr(
                            'REMOTE_ADDR',
                            $this->getIp()
                        ),
                        'ip_proxy'      => Server::getFirstExisting(
                            [
                                'HTTP_CLIENT_IP',
                                'HTTP_X_FORWARDED_FOR'
                            ],
                            'str',
                            $this->getIpProxy()
                        ),
                        'lang'          => strtolower(
                            substr(
                                Server::getStr(
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
