<?php
    /**
    * @package   project/core
    * @version   1.0.0 14.05.2018
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

        public static function getLocalesNames(): array {
            $res = [];
            foreach (static::getLocalesPathes() as $path) {
                foreach (Dir::init($path)->files() as $file) {
                    if ($file->getExt() == static::LOCALES_EXT) {
                        $res[] = $file->getName();
                    }
                }
            }
            $res = array_unique($res);
            sort($res);
            return $res;
        }

        public static function getLocalesPathes(): array {
            return static::$locales_pathes;
        }

        public static function init(
            string $time          = 'now',
            string $timezone_name = ''
        ): \Ada\Core\DateTime {
            return new static(...func_get_args());
        }

        public static function preset(
            string $default_timezone_name = '',
            string $default_format        = '',
            string $default_locale_name   = '',
            array  $locales_pathes        = []
        ): bool {
            if (static::$inited) {
                return false;
            }
            if ($default_timezone_name !== '') {
                DateTime\TimeZone::init($default_timezone_name);
                date_default_timezone_set($default_timezone_name);
            }
            if ($default_format !== '') {
                static::$default_format = $default_format;
            }
            if ($default_locale_name !== '') {
                static::$default_locale_name = Clean::cmd($default_locale_name);
            }
            if ($locales_pathes !== []) {
                static::$locales_pathes = array_map(
                    '\Ada\Core\Clean::path',
                    array_merge(static::$locales_pathes, $locales_pathes)
                );
            }
            return true;
        }

        public function __construct(
            string $time          = 'now',
            string $timezone_name = ''
        ) {
            static::preset();
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
            if ($cached && isset(static::$cache[$locale_name])) {
                return static::$cache[$locale_name];
            }
            $res = [];
            foreach (static::getLocalesPathes() as $path) {
                $res = Arr::init($res)->mergeRecursive(
                    File::init(
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
            return static::$cache[$locale_name] = $res;
        }

    }
