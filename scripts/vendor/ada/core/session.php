<?php
    /**
    * @package   ada/core
    * @version   1.0.0 17.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class Session extends Session\Input {

        const
            DEFAULT_NAMESPACE = '_',
            NAMESPACE_PREFIX  = '_',
            SELF_NAMESPACE    = '9be28143618f21b9456528c2ee825873';

        protected
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
            ],
            $new        = true,
            $read_only  = false;

        public static function init(
            array           $ini_params = [],
            Session\Handler $handler    = null
        ): self {
            static $res;
            return $res ?? $res = new static($ini_params, $handler);
        }

        protected function __construct(
            array           $ini_params = [],
            Session\Handler $handler    = null
        ) {
            $this->new        = !Cookie::getBool($this->generateName());
            $this->ini_params = Type::set(
                array_merge(
                    $this->ini_params,
                    [
                        'cookie_secure' => Url::init()->isSSL(),
                        'save_path'     => Clean::path(session_save_path())
                    ],
                    $ini_params
                )
            );
            if ($handler && session_set_save_handler($handler)) {
                $this->handler = $handler;
            }
            session_name($this->generateName());
        }

        public function abort(): bool {
            if (!$this->isStarted()) {
                return false;
            }
            session_abort();
            return true;
        }

        public function check(): bool {
            if (!$this->isStarted()) {
                return false;
            }
            if ($this->isNew()) {
                return true;
            }
            if (
                (
                    (
                        strtotime(
                            static::getString(
                                'last_stop_datetime',
                                '',
                                static::SELF_NAMESPACE
                            )
                        )
                        +
                        $this->getIniParam('gc_maxlifetime')
                    )
                    <
                    DateTime::init()->getTimestamp()
                ) ||
                (
                    static::getString(
                        'browser_signature',
                        '',
                        static::SELF_NAMESPACE
                    )
                    !=
                    Client::init()->getSignature()
                )
            ) {
                return false;
            }
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
                    Cookie::del($this->getName())
                ]
            );
        }

        public function getHandler(): Session\Handler {
            return $this->handler;
        }

        public function getId(): string {
            return session_id();
        }

        public function getIniParam(string $name) {
            return $this->ini_params[strtolower(Clean::cmd($name))] ?? null;
        }

        public function getIniParams(): array {
            return $this->ini_params;
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
            $this->read_only = $read_only;
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
            if (!$this->check()) {
                $this->delete();
                return false;
            }
            return $res;
        }

        public function stop(): bool {
            if (!$this->isStarted()) {
                return false;
            }
            static::set(
                'last_stop_datetime',
                DateTime::init()->format(),
                static::SELF_NAMESPACE
            );
            static::set(
                'browser_signature',
                Client::init()->getSignature(),
                static::SELF_NAMESPACE
            );
            session_write_close();
            $this->read_only = false;
            return true;
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
                Clean::cmd(static::NAMESPACE_PREFIX) .
                Clean::cmd($namespace == '' ? static::DEFAULT_NAMESPACE : $namespace)
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
