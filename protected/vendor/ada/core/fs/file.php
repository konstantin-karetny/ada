<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Fs;

    class File extends \Ada\Core\Proto {

        protected
            $path = '';

        public static function init(string $path): \Ada\Core\Fs\File {
            return new static($path);
        }

        protected function __construct(string $path) {
            $this->path = Path::clean($path);
        }

        public function copy(
            string $path,
            bool   $validate_ext = true
        ): \Ada\Core\Fs\File {
            $res = static::init(Path::clean($path, $validate_ext));
            $dir = $res->getDir();
            if (!$dir->exists() && !$dir->create()) {
                return $res;
            }
            @copy($this->getPath(), $res->getPath());
            return $res;
        }

        public function create(string $contents = ''): bool {
            return $this->write($contents);
        }

        public function delete(): bool {
            $this->setPerms(0777);
            return (bool) @unlink($this->getPath());
        }

        public function exists(): bool {
            return is_file($this->getPath());
        }

        public function getBasename(): string {
            return pathinfo($this->getPath(), PATHINFO_BASENAME);
        }

        public function getDir(): \Ada\Core\Fs\Dir {
            return Dir::init(
                trim(
                    pathinfo($this->getPath(), PATHINFO_DIRNAME),
                    '.'
                )
            );
        }

        public function getEditTime(): int {
            return (int) @filemtime($this->getPath());
        }

        public function getExt(): string {
            return pathinfo($this->getPath(), PATHINFO_EXTENSION);
        }

        public function getMimeType(string $default = ''): string {
            return
                $this->exists() && class_exists('finfo')
                    ? $this->mime_type = (new \finfo())->file(
                        $this->getPath(),
                        FILEINFO_MIME_TYPE
                    )
                    : $default;
        }

        public function getName(): string {
            return pathinfo($this->getPath(), PATHINFO_FILENAME);
        }

        public function getPath(): string {
            return $this->path;
        }

        public function getPerms(): int {
            return (int) @fileperms($this->getPath());
        }

        public function getSize(): int {
            return (int) @filesize($this->getPath());
        }

        public function isReadable(): bool {
            return is_readable($this->getPath());
        }

        public function isWritable(): bool {
            return is_writable($this->getPath());
        }

        public function move(
            string $path,
            bool   $validate_ext = true
        ): \Ada\Core\Fs\File {
            $res = static::init(Path::clean($path, $validate_ext));
            $dir = $res->getDir();
            if (!$dir->exists() && !$dir->create()) {
                return $res;
            }
            @rename($this->getPath(), $res->getPath());
            return $res;
        }

        public function parseIni(
            bool $process_sections = true,
            int  $scanner_mode     = INI_SCANNER_TYPED
        ): array {
            $res = @parse_ini_file(
                $this->getPath(),
                $process_sections,
                $scanner_mode
            );
            return is_array($res) ? $res : [];
        }

        public function read(
            int $offset  = 0,
            int $maxlen  = null
        ): string {
            $args = [
                $this->getPath(),
                false,
                null,
                $offset
            ];
            if ($maxlen) {
                array_push($args, $maxlen);
            }
            return (string) @file_get_contents(...$args);
        }

        public function setEditTime(int $time = 0): bool {
            return (bool) @touch(
                $this->getPath(),
                $time > 0 ? $time : \Ada\Core\DateTime::init()->getTimestamp()
            );
        }

        public function setPerms(int $mode): bool {
            return (bool) @chmod($this->getPath(), $mode);
        }

        public function write(
            string $contents,
            bool   $append       = false,
            bool   $validate_ext = true
        ): bool {
            $this->path = Path::clean($this->getPath(), $validate_ext);
            $dir        = $this->getDir();
            if (!$dir->exists() && !$dir->create()) {
                return false;
            }
            @file_put_contents(
                $this->getPath(),
                $contents,
                $append ? FILE_APPEND : 0
            );
            return $this->exists();
        }

    }
