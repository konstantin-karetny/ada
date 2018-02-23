<?php
    /**
    * @package   ada/core
    * @version   1.0.0 18.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Session extends InputSession {

        protected const
            SELF_NAMESPACE    = '_SESS',
            DEFAULT_NAMESPACE = '_',
            NAMESPACE_PREFIX  = '_';

        protected
            $new        = true,
            $handler    = null,
            $ini_params = [
                'cache_limiter'    => 'none',
                'cookie_domain'    => '',
                'cookie_lifetime'  => 0,
                'cookie_httponly'  => true,
                'cookie_path'      => '/',
                'cookie_secure'    => true,
                'gc_maxlifetime'   => 900,
                'save_handler'     => 'files',
                'save_path'        => '',
                'use_cookies'      => true,
                'use_only_cookies' => true,
                'use_strict_mode'  => true,
                'use_trans_sid'    => false
            ];

        public static function init(bool $cached = true): self {
            static $res;
            return $res && $cached ? $res : ($res = new self);
        }

        protected function __construct() {
            $this->new        = !Cookie::getBool($this->generateName());
            $this->ini_params = array_merge(
                $this->ini_params,
                [
                    'cookie_secure' => Url::init()->isSSL(),
                    'save_path'     => Clean::path(session_save_path())
                ]
            );
            session_name($this->generateName());
        }

        public function abort(): bool {
            if (!$this->isStarted()) {
                return false;
            }
            session_abort();
            return true;
        }

        public function clear(): bool {
            if (!$this->isStarted()) {
                return false;
            }
            session_unset();
            return session_destroy();
        }

        public function delete(): bool {
            if (!$this->isStarted()) {
                return false;
            }
            return !in_array(
                false,
                [
                    $this->clear(),
                    Cookie::unset($this->getName())
                ]
            );
        }

        public function regenerateId($delete_old_session = false): bool {
            if (!$this->isStarted()) {
                return false;
            }
            return session_regenerate_id($delete_old_session);
        }

        public function restart(): bool {
            return !in_array(
                false,
                [
                    $this->stop(),
                    $this->start()
                ]
            );
        }

        public function start(bool $read_only = false): bool {
            if ($this->isStarted()) {
                return true;
            }
            register_shutdown_function([$this, 'stop']);
            $ini_params = $this->getIniParams();
            if ($this->handler) {
                unset($ini_params['save_handler']);
            }
            $res = session_start(
                $ini_params +
                [
                    'read_and_close' => $read_only
                ]
            );
            if ($this->isNew()) {
                self::set('last_activity', 'yy', self::SELF_NAMESPACE);
            }
            return $res;
        }

        public function stop(): bool {
            if (!$this->isStarted()) {
                return false;
            }
            session_write_close();
            return true;
        }

        public function getId(): string {
            return session_id();
        }

        public function getIniParam(string $name): array {
            return $this->ini_params[strtolower(Clean::cmd($name))] ?? null;
        }

        public function getIniParams(): array {
            return $this->ini_params;
        }

        public function getName(): string {
            return session_name();
        }

        public function getHandler(): SessionHandler {
            return $this->handler;
        }

        public function isNew(): bool {
            return $this->new;
        }

        public function getState(): int {
            return session_status();
        }

        public function isStarted(): bool {
            return $this->getState() == 2;
        }

        public function setHandler(SessionHandler $handler): bool {
            if (!session_set_save_handler($handler)) {
                return false;
            }
            $this->handler = $handler;
            return true;
        }

        public function setIniParam(string $name, string $param) {
            $this->ini_params[strtolower(Clean::cmd($name))] = Type::set($param);
        }

        public function setIniParams(array $params) {
            foreach ($params as $name => $param) {
                $this->setIniParam($name, $param);
            }
        }

        protected function generateName(): string {
            return md5(Url::init()->getHost());
        }

        protected function getFile(): File {
            return File::init(
                $this->ini_params['save_path'] . '/sess_' . $this->getId()
            );
        }

        protected function namespaceFull(string $namespace): string {
            return strtoupper(
                Clean::cmd(self::NAMESPACE_PREFIX) .
                Clean::cmd($namespace == '' ? self::DEFAULT_NAMESPACE : $namespace)
            );
        }

        public function __debugInfo() {
            var_dump($this);
            return [
                'name'  => $this->getName(),
                'id'    => $this->getId(),
                'state' => $this->getState()
            ];
        }

    }
