<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class DateTime extends \DateTime {

        const
            LOCALES_EXT          = 'ini';

        protected static
            $cache               = [],
            $default_format      = 'Y-m-d H:i:s',
            $default_locale_name = 'en',
            $inited              = false,
            $locales_pathes      = [
                __DIR__ . '/datetime/locales'
            ];

        public static function getDefaultFormat(): string {
            return static::$default_format;
        }

        public static function getDefaultLocaleName(): string {
            return static::$default_locale_name;
        }

        public static function getDefaultTimeZone(): \Ada\Core\DateTime\TimeZone {
            return DateTime\TimeZone::init(date_default_timezone_get());
        }

        public static function getLocalesNames(bool $cached = true): array {
            if ($cached && isset(static::$cache['locales_names'])) {
                return static::$cache['locales_names'];
            }
            $res = [];
            foreach (static::getLocalesPathes() as $path) {
                foreach (Fs\Dir::init($path)->files() as $file) {
                    if ($file->getExt() == static::LOCALES_EXT) {
                        $res[] = $file->getName();
                    }
                }
            }
            return static::$cache['locales_names'] = array_unique(
                Type\Arr::init($res)->sort()
            );
        }

        public static function getLocalesPathes(): array {
            return static::$locales_pathes;
        }

        public static function init(
            string $time          = 'now',
            string $timezone_name = ''
        ): \Ada\Core\DateTime {
            return new static($time, $timezone_name);
        }

        public static function isInited(): bool {
            return static::$inited;
        }

        public static function preset(array $params): bool {
            if (static::$inited) {
                return false;
            }
            foreach ($params as $k => $v) {
                switch (Clean::cmd($k)) {
                    case 'default_timezone_name':
                        DateTime\TimeZone::init($v);
                        date_default_timezone_set($v);
                        break;
                    case 'default_format':
                        static::$default_format      = Clean::str($v);
                        break;
                    case 'default_locale_name':
                        static::$default_locale_name = Clean::cmd($v);
                        break;
                    case 'locales_pathes':
                        static::$locales_pathes      = array_unique(
                            array_merge(
                                static::$locales_pathes,
                                Clean::values($v, 'path')
                            )
                        );
                        break;
                }
            }
            static::$cache = [];
            return true;
        }

        public function __construct(
            string $time          = 'now',
            string $timezone_name = ''
        ) {
            static::$locales_pathes = Clean::values(
                static::$locales_pathes,
                'path'
            );
            parent::__construct(
                $time,
                DateTime\TimeZone::init(
                    $timezone_name === ''
                        ? date_default_timezone_get()
                        : $timezone_name
                )
            );
            static::$inited = true;
        }

        public function __debugInfo() {
            var_dump($this);
            return (new \ReflectionClass($this))->getStaticProperties();
        }

        public function format(
                   $format      = '',
            string $locale_name = ''
        ): string {
            $format = $format === '' ? static::getDefaultFormat() : $format;
            if ($format == 'r') {
                $format = static::RFC2822;
            }
            $locale_name = Clean::cmd(
                $locale_name === ''
                    ? static::getDefaultLocaleName()
                    : $locale_name
            );
            $res    = '';
            $locale = $this->getLocale($locale_name);
            if (!$locale) {
                return parent::format($format);
            }
            for ($i = 0; $i < strlen($format); $i++) {
                if ($i && $format[$i - 1] == '\\') {
                    $res .= $format[$i];
                    continue;
                }
                $char = $format[$i];
                switch ($char) {
                    case 'D':
                        $sub = $locale[$char][$this->format('N')];
                        break;
                    case 'F':
                        $sub = $locale[$char][$this->format('n')];
                        break;
                    case 'l':
                        $sub = $locale[$char][$this->format('N')];
                        break;
                    case 'M':
                        if (($format[$i + 1] ?? '') === 'S') {
                            $sub = $locale[$char . 'S'][$this->format('n')];
                            $i++;
                            break;
                        }
                        $sub = $locale[$char][$this->format('n')];
                        break;
                    case 'S':
                        $day = $this->format('j');
                        $sub = $locale[$char][$day > 3 ? 4 : $day];
                        break;
                    default:
                        $res .= $char;
                        continue 2;
                }
                $res .= '\\' . implode('\\', str_split($sub));
            }
            return parent::format($res);
        }

        public function getLocale(
            string $locale_name = '',
            bool   $cached      = true
        ): array {
            $locale_name = Clean::cmd(
                $locale_name === ''
                    ? static::getDefaultLocaleName()
                    : $locale_name
            );
            if ($cached && isset(static::$cache['locales'][$locale_name])) {
                return static::$cache['locales'][$locale_name];
            }
            $res = [];
            foreach (static::getLocalesPathes() as $path) {
                $res = Type\Arr::init($res)->mergeRecursive(
                    Fs\File::init(
                        $path . '/' . $locale_name . '.' . static::LOCALES_EXT
                    )->parseIni()
                );
            }
            if (!$res) {
                throw new Exception(
                    'No locale with name \'' . $locale_name . '\'',
                    1
                );
            }
            static::$cache['locales'] = static::$cache['locales'] ?? [];
            return static::$cache['locales'][$locale_name] = $res;
        }

    }
