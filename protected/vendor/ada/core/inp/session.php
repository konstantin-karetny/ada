<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Inp;

    class Session extends Session\Inp {

        const
            DEFAULT_NAMESPACE = '_',
            NAMESPACE_PREFIX  = '#__';

        protected static
            $handler          = null,
            $ini_params       = [
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
            ],
            $inited           = false,
            $system_namespace = '';

        protected
            $new              = true,
            $read_only        = false;

        public static function getHandler() {
            return static::$handler;
        }

        public static function getIniParam(string $name, $default = null) {
            return static::getIniParams()[\Ada\Core\Clean::cmd($name)] ?? $default;
        }

        public static function getIniParams(): array {
            return static::$ini_params;
        }

        public static function &getStorage(): array {
            static::init()->start();
            return parent::getStorage();
        }

        public static function init(): \Ada\Core\Inp\Session {
            static $res;
            return $res ?? $res = new static;
        }

        public static function isInited(): bool {
            return static::$inited;
        }

        public static function preset(array $params): bool {
            if (static::$inited) {
                return false;
            }
            foreach ($params as $k => $v) {
                switch (\Ada\Core\Clean::cmd($k)) {
                    case 'handler':
                        if (!session_set_save_handler($v)) {
                            return false;
                        }
                        static::$handler = $v;
                        break;
                    case 'ini_params':
                        foreach (\Ada\Core\Types::set($v, 'arr') as $kk => $vv) {
                            $kk = \Ada\Core\Clean::cmd($kk);
                            if (!key_exists($kk, static::getIniParams())) {
                                continue;
                            }
                            static::$ini_params[$kk] = \Ada\Core\Types::set(
                                $vv,
                                \Ada\Core\Types::get(static::getIniParam($kk)),
                                false
                            );
                        }
                        break;
                }
            }
            return true;
        }

        protected function __construct() {
            static::$system_namespace = \Ada\Core\Type\Str::init(__FILE__)->hash();
            static::$ini_params       = array_merge(
                static::getIniParams(),
                [
                    'cookie_secure' => \Ada\Core\Url::init()->isSSL(),
                    'save_path'     => \Ada\Core\Clean::path(session_save_path())
                ]
            );
            session_name($this->generateName());
            $this->new      = !Cookie::getBool($this->getName());
            static::$inited = true;
        }

        public function __debugInfo() {
            var_dump($this);
            return [
                'name'  => $this->getName(),
                'id'    => $this->getId(),
                'state' => $this->getState()
            ];
        }

        public function abort(): bool {
            if (!$this->isStarted()) {
                return true;
            }
            session_abort();
			$this->read_only = false;
            return true;
        }

        public function check(): bool {
            if (!$this->isStarted()) {
                return true;
            }
            if ($this->isNew()) {
                return true;
            }
            if (
                (
                    (
                        strtotime(
                            static::getStr(
                                'last_stop_datetime',
                                '',
                                static::$system_namespace
                            )
                        )
                        +
                        static::getIniParam('gc_maxlifetime')
                    )
                    <
                    \Ada\Core\DateTime::init()->getTimestamp()
                ) ||
                (
                    static::getStr(
                        'browser_signature',
                        '',
                        static::$system_namespace
                    )
                    !=
                    \Ada\Core\Client::init()->getSignature()
                )
            ) {
                return false;
            }
            return true;
        }

        public function clear(): bool {
            if (
                (!$this->isStarted() && !$this->start()) ||
                ($this->isReadOnly() && !$this->restart(false))
            ) {
                return false;
            }
			session_unset();
			$res             = session_destroy();
			$this->read_only = false;
            return $res;
        }

        public function delete(): bool {
            if (!$this->isStarted() && !$this->start()) {
                return false;
            }
            return !in_array(
                false,
                [
                    $this->clear(),
                    Cookie::drop($this->getName())
                ]
            );
        }

        public function getId(): string {
            return session_id();
        }

        public function getName(): string {
            return session_name();
        }

        public function getState(): int {
            return session_status();
        }

        public function isNew(): bool {
            return $this->new;
        }

        public function isReadOnly(): bool {
            return $this->read_only;
        }

        public function isStarted(): bool {
            $state = $this->getState();
            return $state == 2 || ($state == 1 && $this->isReadOnly());
        }

        public function regenerateId($delete_old_session = false): bool {
            if (!$this->isStarted() && !$this->start()) {
                return false;
            }
            return session_regenerate_id($delete_old_session);
        }

        public function restart(bool $read_only = false): bool {
            return !in_array(
                false,
                [
                    $this->stop(),
                    $this->start($read_only)
                ]
            );
        }

        public function start(bool $read_only = false): bool {
            if ($this->isStarted()) {
                return true;
            }
            register_shutdown_function([$this, 'stop']);
            $ini_params = static::getIniParams();
            if (static::getHandler()) {
                unset($ini_params['save_handler']);
            }
            $res = session_start(
                $ini_params +
                [
                    'read_and_close' => $read_only
                ]
            );
			if ($res) {
				$this->read_only = $read_only;
			}
            if (!$this->check()) {
                $this->delete();
                return false;
            }
            return $res;
        }

        public function stop(): bool {
            if (!$this->isStarted()) {
                return true;
            }
            static::set(
                'last_stop_datetime',
                \Ada\Core\DateTime::init()->format(),
                static::$system_namespace
            );
            static::set(
                'browser_signature',
                \Ada\Core\Client::init()->getSignature(),
                static::$system_namespace
            );
            session_write_close();
			$this->read_only = false;
            return true;
        }

        protected function generateName(): string {
            return \Ada\Core\Type\Str::init(__DIR__)->hash();
        }

        protected function getFile(): File {
            return \Ada\Core\Fs\File::init(
                static::getIniParam('save_path') . '/sess_' . $this->getId()
            );
        }

        protected function namespaceFull(string $namespace): string {
            return strtoupper(
                static::NAMESPACE_PREFIX .
                ($namespace == '' ? static::DEFAULT_NAMESPACE : $namespace)
            );
        }

    }
