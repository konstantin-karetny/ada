<?php
    /**
    * @package   project/core
    * @version   1.0.0 11.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class DateTime extends \DateTime {

        protected static
            $default_format = 'Y-m-d H:i:s',
            $inited         = false;

        protected
            $locales_ext    = 'ini',
            $locales_path   = __DIR__ . '/datetime/locales';

        public static function init(
            string $time          = 'now',
            string $timezone_name = ''
        ): \Ada\Core\DateTime {
            return new static(...func_get_args());
        }

        public static function preset(
            string $default_timezone_name = '',
            string $default_format        = ''
        ): bool {
            if (static::$inited) {
                return false;
            }
            if ($default_timezone_name !== '') {
                TimeZone::init($default_timezone_name);
                date_default_timezone_set($default_timezone_name);
            }
            if ($default_format !== '') {
                static::$default_format = $default_format;
            }
            return true;
        }

        public function __construct(
            string $time          = 'now',
            string $timezone_name = ''
        ) {
            parent::__construct(
                $time,
                TimeZone::init(
                    $timezone_name === ''
                        ? date_default_timezone_get()
                        : $timezone_name
                )
            );
            $this->locales_path = Clean::path($this->locales_path);
            static::$inited     = true;
        }

        public function format($format = '', string $locale_id = 'en'): string {
            $format = $format === '' ? $this->getDefaultFormat() : $format;
            if ($format == 'r') {
                return $this->format(
                    str_replace('M', 'MS', static::RFC2822),
                    $locale_id
                );
            }
            $res    = '';
            $locale = $this->getLocale($locale_id);
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
                        if (($format[$i + 1] ?? '') == 'S') {
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
                        $sub = $char;
                }
                $res .= implode('\\', str_split($sub));
            }
            return parent::format($res);
        }

        public function getDefaultFormat(): string {
            return static::$default_format;
        }

        public function getLocale(string $locale_id = 'en'): array {
            return File::init(
                $this->locales_path . '/' .
                $locale_id . '.' .
                $this->locales_ext
            )->parseIni();
        }

        public function getDefaultTimeZone(): \Ada\Core\TimeZone {
            return TimeZone::init(date_default_timezone_get());
        }

    }
