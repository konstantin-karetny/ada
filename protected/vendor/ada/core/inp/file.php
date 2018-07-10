<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Inp;

    class File extends \Ada\Core\Proto {

        const
            ERRORS      = [
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload',
                UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the \'MAX_FILE_SIZE\' directive that was specified in the HTML form',
                UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the maximum failszie',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary directory',
                UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success',
                UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
                10                    => 'Unknown error'
            ];

        protected
            $basename   = '',
            $error_code = 0,
            $error_msg  = '',
            $mime_type  = '',
            $path       = '',
            $size       = 0;

        public static function init(array $params): \Ada\Core\Inp\File {
            return new static($params);
        }

        protected function __construct(array $params) {
            foreach ($params as $k => $v) {
                $v = \Ada\Core\Types::set($v);
                switch ($k) {
                    case 'name':
                        $this->basename      = \Ada\Core\Clean::path($v, true);
                        break;
                    case 'type':
                        $this->mime_type     = $v;
                        break;
                    case 'tmp_name':
                        $this->path          = $v;
                        break;
                    case 'error':
                        $this->error_code    = isset(static::ERRORS[$v]) ? $v : 10;
                        if ($this->getErrorCode()) {
                            $this->error_msg = static::ERRORS[$this->getErrorCode()];
                        }
                        break;
                    default:
                        $this->$k            = $v;
                }
            }
            $this->mime_type = \Ada\Core\Fs\File::init(
                $this->getPath()
            )->getMimeType(
                $this->getMimeType()
            );
        }

        public function getBasename(): string {
            return $this->basename;
        }

        public function getErrorCode(): int {
            return $this->error_code;
        }

        public function getErrorMsg(): string {
            return $this->error_msg;
        }

        public function getExt(): string {
            return \Ada\Core\Fs\File::init($this->getBasename())->getExt();
        }

        public function getMimeType(): string {
            return $this->mime_type;
        }

        public function getName(): string {
            return \Ada\Core\Fs\File::init($this->getBasename())->getName();
        }

        public function getPath(): string {
            return $this->path;
        }

        public function getSize(): string {
            return $this->size;
        }

        public function isUploded(): bool {
            return (bool) is_uploaded_file($this->getPath());
        }

        public function save(
            string $path,
            bool   $validate_ext = true
        ): \Ada\Core\Fs\File {
            $res = \Ada\Core\Fs\File::init(
                \Ada\Core\Clean::path($path, $validate_ext)
            );
            $dir = $res->getDir();
            if (
                $this->getErrorCode() ||
                (
                    !$dir->exists() &&
                    !$dir->create()
                )
            ) {
                return $res;
            }
            @move_uploaded_file($this->getPath(), $res->getPath());
            return $res;
        }

    }
