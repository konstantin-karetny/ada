<?php
    /**
    * @package   ada/core
    * @version   1.0.0 07.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class UploadedFile extends Proto {

        protected
            $basename   = '',
            $error_code = 0,
            $mime_type  = '',
            $path       = '',
            $size       = 0;

        public static function init(array $params): self {
            return new self($params);
        }

        public function __construct(array $params) {
            foreach ($params as $k => $v) {
                $v = trim($v);
                switch ($k) {
                    case 'name':
                        $this->basename   = strtolower(
                            preg_replace(
                                [
                                    '/\.jpeg$/'
                                ],
                                [
                                    '.jpg'
                                ],
                                Path::clean($v)
                            )
                        );
                        break;
                    case 'type':
                        $this->mime_type  = $v;
                        break;
                    case 'size':
                        $this->size       = (int) $v;
                        break;
                    case 'tmp_name':
                        $this->path       = $v;
                        break;
                    case 'error':
                        $this->error_code = (int) $v;
                        break;
                }
            }
            $this->mime_type = File::init($this->path)->getMimeType($this->mime_type);
        }

        public function getBasename(): string {
            return $this->basename;
        }

        public function getErrorCode(): int {
            return $this->error_code;
        }

        public function getErrorMessage(): string {
            switch ($this->error_code) {
                case UPLOAD_ERR_OK:
                    return 'There is no error, the file uploaded with success';
                    break;
                case UPLOAD_ERR_INI_SIZE:
                    return (
                        'The uploaded file exceeds the maximum failszie at ' .
                        ini_get('upload_max_filesize')
                    );
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    return (
                        'The uploaded file exceeds the \'MAX_FILE_SIZE\' ' .
                        'directive that was specified in the HTML form'
                    );
                    break;
                case UPLOAD_ERR_PARTIAL:
                    return 'The uploaded file was only partially uploaded';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    return 'No file was uploaded';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    return 'Missing a temporary folder';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    return 'Failed to write file to disk';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    return 'A PHP extension stopped the file upload';
                    break;
            }
        }

        public function getExt(): string {
            return File::init($this->basename)->getExt();
        }

        public function getMimeType(): string {
            return $this->mime_type;
        }

        public function getName(): string {
            return File::init($this->basename)->getName();
        }

        public function getPath(): string {
            return $this->path;
        }

        public function getSize(): string {
            return $this->size;
        }

        public function isUploded(): bool {
            return (bool) is_uploaded_file($this->path);
        }

        public function save(string $path): bool {
            return (bool) move_uploaded_file($this->path, Path::clean($path));
        }

    }
