<?php
    /**
    * @package   ada/core
    * @version   1.0.0 05.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Client extends Proto {

        protected
            $browser = '',
            $ip      = '',
            $lang    = 'en';

        public static function init(bool $cached = true): self {
            static $res;
            return $res && $cached ? $res : ($res = new self);
        }

        protected function __construct(bool $cached = true) {
            $this->browser = Server::getString('HTTP_USER_AGENT');
            foreach ([
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'REMOTE_ADDR'
            ] as $key) {
                $ip = Server::getString($key);
                if ($ip !== '') {
                    $this->ip = trim($_SERVER[$key]);
                    break;
                }
            }
            $this->lang = strtolower(
                substr(
                    trim(Server::getString('HTTP_ACCEPT_LANGUAGE', $this->lang)),
                    0,
                    2
                )
            );
        }

        public function getBrowser(): string {
            return $this->browser;
        }

        public function getIp(): string {
            return $this->ip;
        }

        public function getLang(): string {
            return $this->lang;
        }

        public function getSignature(): string {

            //add all HTTP_ s
            //http://php.net/manual/ru/reserved.variables.server.php
            //https://www.mind-it.info/2012/08/01/using-browser-fingerprints-for-session-encryption/


            exit(var_dump( $this ));

        }

    }
