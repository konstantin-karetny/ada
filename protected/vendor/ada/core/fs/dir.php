<?php
    /**
    * @package   project/core
    * @version   1.0.0 30.10.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Fs;

    class Dir extends \Ada\Core\Proto {

        protected
            $path = '';

        public static function init(string $path): \Ada\Core\Fs\Dir {
            return new static($path);
        }

        protected function __construct(string $path) {
            $this->path = Path::clean($path);
        }

        public function contents(): array {
            return array_merge(
                $this->dirs(),
                $this->files()
            );
        }

        public function copy(string $path): bool {
            if (!$this->exists()) {
                return false;
            }
            $dir = static::init($path);
            if ($dir->exists() || !$dir->create()) {
                return false;
            }
            $res  = [];
            $path = $dir->getPath();
            foreach ($this->dirs() as $subdir) {
                $res[] = static::init(
                    $path . Path::DS . $subdir->getName()
                )->create();
            }
            foreach ($this->files() as $file) {
                $res[] = $file->copy(
                    $path . Path::DS . $file->getBasename()
                );
            }
            return !in_array(false, $res);
        }

        public function create(int $mode = 0755): bool {
            $parent = $this->getDir();
            if (!$parent->exists()) {
                $parent->create($mode);
            }
            return @mkdir($this->path, $mode);
        }

        public function delete(): bool {
            $this->setPerms(0777);
            foreach ($this->dirs() as $dir) {
                $dir->delete();
            }
            foreach ($this->files() as $file) {
                $file->delete();
            }
            return (bool) @rmdir($this->path);
        }

        public function dirs(): array {
            $res = [];
            if (!$this->exists()) {
                return $res;
            }
            foreach (new \DirectoryIterator($this->path) as $iter) {
                if ($iter->isDir()) {
                    $path       = Path::clean($iter->getPathname());
                    $res[$path] = static::init($path);
                }
            }
            return \Ada\Core\Type\Arr::init($res)->ksort();
        }

        public function exists(): bool {
            return is_dir($this->path);
        }

        public function files(): array {
            $res = [];
            if (!$this->exists()) {
                return $res;
            }
            foreach (new \DirectoryIterator($this->path) as $iter) {
                if ($iter->isFile()) {
                    $path       = Path::clean($iter->getPathname());
                    $res[$path] = File::init($path);
                }
            }
            return \Ada\Core\Type\Arr::init($res)->ksort();
        }

        public function getDir(): \Ada\Core\Fs\Dir {
            return static::init(pathinfo($this->path, PATHINFO_DIRNAME));
        }

        public function getEditTime(): int {
            return (int) @stat($this->path)['mtime'];
        }

        public function getName(): string {
            return pathinfo($this->path, PATHINFO_FILENAME);
        }

        public function getPath(): string {
            return $this->path;
        }

        public function getPerms(): int {
            return (int) @fileperms($this->path);
        }

        public function getSize(): int {
            $res = 0;
            if (!$this->exists()) {
                return $res;
            }
            foreach(new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->path,
                    \FilesystemIterator::SKIP_DOTS
                )
            ) as $iter){
                $res += $iter->getSize();
            }
            return $res;
        }

        public function isReadable(): bool {
            return is_readable($this->path);
        }

        public function isWritable(): bool {
            return is_writable($this->path);
        }

        public function move(string $path): bool {
            if (!$this->copy($path)) {
                return false;
            }
            return $this->delete();
        }

        public function setEditTime(int $time = 0): bool {
            return (bool) @touch(
                $this->path,
                $time > 0 ? $time : \Ada\Core\DateTime::init()->getTimestamp()
            );
        }

        public function setPerms(int $mode): bool {
            return (bool) @chmod($this->path, $mode);
        }

    }
