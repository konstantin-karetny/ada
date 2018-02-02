<?php
    /**
    * @package   ada/core
    * @version   1.0.0 02.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class File extends Singleton {

        protected
            $path = '';

        public static function init(string $id, bool $cached = true): self {
            return parent::init($id, $cached);
        }

        protected function __construct(string $path) {
            $this->path = Clean::path($path);
        }

        public function delete(): bool {
            $this->setPerms(0777);
            return (bool) @unlink($this->path);
        }

        public function exists(): bool {
            return is_file($this->path);
        }

        public function getBasename(): string {
            return basename($this->path);
        }

        public function getExt(): string {
            return pathinfo($this->path, PATHINFO_EXTENSION);
        }

        public function getName(): string {
            return pathinfo($this->path, PATHINFO_FILENAME);
        }

        public function getPerms() {
            return fileperms($this->path);
        }

        public function isReadable(): bool {
            return is_readable($this->path);
        }

        public function isWritable(): bool {
            return is_writable($this->path);
        }

        public function copy(string $path): bool {
            return (bool) @copy($this->path, $path);
        }

        public function move(string $path): bool {
            if (!@rename($this->path, $path)) {
                return false;
            }
            $this->path = Clean::path($path);
            return true;
        }

        public function read(
            int $offset  = 0,
            int $maxlen  = null
        ):  string {
            return (string) @(
                is_null($maxlen)
                ? file_get_contents($this->path, false, null, $offset)
                : file_get_contents($this->path, false, null, $offset, $maxlen)
            );
        }

        public function write(
            string $contents,
            bool   $append = false
        ):  bool {
            return (bool) @file_put_contents(
                $this->path,
                $contents,
                $append ? FILE_APPEND : 0
            );
        }

        public function setPerms(int $mode): bool {
            return (bool) @chmod($this->path, $mode);
        }

    }
