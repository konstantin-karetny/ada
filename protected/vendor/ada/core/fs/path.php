<?php
    /**
    * @package   project/core
    * @version   1.0.0 07.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core\Fs;

    class Path extends \Ada\Core\Proto {

        const
            DS       = '/',
            EXTS_MAP = [
                'jpeg' => 'jpg'
            ];

        public static function clean(
            string $path,
            bool   $validate_ext = false
        ): string {
            $res = strtolower(
                (string) preg_replace(
                    '/[\/\\\]+/',
                    static::DS,
                    trim($path, " \t\n\r\0\x0B\\/")
                )
            );
            return
                $validate_ext
                    ? (string) preg_replace(
                        array_map(
                            function($el) {
                                return '/\.' . $el . '$/';
                            },
                            array_keys(static::EXTS_MAP)
                        ),
                        array_map(
                            function($el) {
                                return '.' . $el;
                            },
                            array_values(static::EXTS_MAP)
                        ),
                        static::clean($path)
                    )
                    : $res;
        }

    }
