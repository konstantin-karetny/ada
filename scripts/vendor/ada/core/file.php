<?php
    /**
    * @package   ada/core
    * @version   1.0.0 05.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class File extends Proto {

        protected
            $path = '';

        public static function init(string $path): self {
            return new self($path);
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

        public function getEditTime(): int {
            return (int) @filemtime($this->path);
        }

        public function getBasename(): string {
            return pathinfo($this->path, PATHINFO_BASENAME);
        }

        public function getDir(): string {
            return pathinfo($this->path, PATHINFO_DIRNAME) . Path::DS;
        }

        public function getExt(): string {
            return pathinfo($this->path, PATHINFO_EXTENSION);
        }

        public function getMimeType(string $default = ''): string {
            return (
                $this->exists() && class_exists('finfo')
                ? $this->mime_type = (new \finfo())->file(
                    $this->path,
                    FILEINFO_MIME_TYPE
                )
                : $default
            );
        }

        public function getName(): string {
            return pathinfo($this->path, PATHINFO_FILENAME);
        }

        public function getPerms() {
            return @fileperms($this->path);
        }

        public function getSize(): int {
            return (int) @filesize($this->path);
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

        public function create(string $contents = ''): bool {
            return $this->write($contents);
        }

        public function move(string $path): bool {
            if (!@rename($this->path, $path)) {
                return false;
            }
            $this->path = Clean::path($path);
            return true;
        }

        public function parseIni(
            bool $process_sections = true,
            int  $scanner_mode     = INI_SCANNER_TYPED
        ) {
            return (array) @parse_ini_file(
                $this->path,
                $process_sections,
                $scanner_mode
            );
        }

        public function read(
            int $offset  = 0,
            int $maxlen  = null
        ): string {
            return (string) @(
                is_null($maxlen)
                ? file_get_contents($this->path, false, null, $offset)
                : file_get_contents($this->path, false, null, $offset, $maxlen)
            );
        }

        public function write(
            string $contents,
            bool   $append = false
        ): bool {
            return (bool) @file_put_contents(
                $this->path,
                $contents,
                $append ? FILE_APPEND : 0
            );
        }

        public function setEditTime(
            int $time        = 0,
            int $access_time = 0
        ): bool {
            return (int) @touch(
                $this->path,
                $time > 0 ? $time : Time::init()->getTimestamp()
            );
        }

        public function setPerms(int $mode): bool {
            return (bool) @chmod($this->path, $mode);
        }

    }
