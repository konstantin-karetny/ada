<?php
    /**
    * @package   ada/tools
    * @version   1.0.0 09.11.2017
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Tools;

    class Uri extends Singleton {

        protected
            $raw      = '',
            $scheme   = '',
            $user     = '',
            $password = '',
            $host     = '',
            $port     = '',
            $path     = '',
            $query    = '',
            $fragment = '',
            $vars     = [],
            $parts    = [
                'scheme',
                'user',
                'password',
                'host',
                'port',
                'path',
                'query',
                'fragment'
            ];

        public static function getInst(
            string $uri = '',
            array  $params = [],
            bool   $cached = true
        ): self {
            return parent::getInst(
                $uri ? Clean::uri($uri) : self::getRaw(),
                $params,
                $cached
            );
        }

        public static function getRaw() {
            $res = 'http';
            if (
                (!empty($_SERVER['HTTPS']) &&
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
            return Clean::uri($res);
        }

        protected function __construct(string $uri) {
            foreach ($this->parse($uri) as $k => $v) {
                $this->$k = $v;
            }
            $this->vars = $this->parseQuery($this->query);
        }

        protected function parse(string $uri): array {
            return array_merge(
                array_fill_keys($this->parts, ''),
                array_map(
                    'trim',
                    parse_url(
                        urldecode($uri)
                    )
                )
            );
        }

        protected function parseQuery(string $query): array {
            $res = [];
            parse_str($query, $res);
            return $res;
        }

    }
