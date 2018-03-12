<?php
    /**
    * @package   ada/core
    * @version   1.0.0 12.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class DateTime extends \DateTime {

        protected static
            $default_format    = '',
            $default_timezone  = null;

        protected
            $locales_path      = __DIR__ . '/datetime/locales',
            $locales_file_ext  = 'ini';

        public static function init(
            string $time                = 'now',
            string $timezone_id         = '',
            string $default_format      = 'Y-m-d H:i:s',
            string $default_timezone_id = null
        ): self {
            return new self(...func_get_args());
        }

        public function __construct(
            string $time                = 'now',
            string $timezone_id         = '',
            string $default_format      = 'Y-m-d H:i:s',
            string $default_timezone_id = null
        ) {
            if (!static::$default_format && $default_format) {
                static::$default_format = $default_format;
            }
            if (!static::$default_timezone && $default_timezone_id) {
                static::$default_timezone = DateTimeZone::init($default_timezone_id);
            }
            $timezone = $timezone_id === '' ? null : DateTimeZone::init($timezone_id);
            if (!$timezone && static::$default_timezone) {
                $timezone = $this->getDefaultTimezone();
            }
            parent::__construct($time, $timezone);
            $this->locales_path = Clean::path($this->locales_path);
        }

        public function format($format = '', string $locale_id = 'en'): string {
            $format = trim($format ? $format : $this->getDefaultFormat());
            if ($format == 'r') {
                return $this->format(
                    str_replace('M', 'MG', static::RFC2822),
                    $locale_id
                );
            }
            if ($locale_id == 'en') {
                return parent::format($format);
            }
            $format2 = '';
            $locale  = $this->getLocale($locale_id);
            for ($i  = 0; $i < strlen($format); $i++) {
                if ($i && $format[$i - 1] == '\\') {
                    $format2 .= $format[$i];
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
                    case 'M' && $format[$i + 1] == 'G':
                        $sub = $locale[$char . 'G'][$this->format('n')];
                        $i++;
                        break;
                    case 'M':
                        $sub = $locale[$char][$this->format('n')];
                        break;
                    case 'S':
                        $day = $this->format('j');
                        $sub = $locale[$char][$day > 3 ? 4 : $day];
                        break;
                    default:
                        $sub = $char;
                }
                $format2 .= implode('\\', str_split($sub));
            }
            return parent::format($format2);
        }

        public function getDefaultFormat(): string {
            return static::$default_format;
        }

        public function getDefaultTimezone(): DateTimeZone {
            return static::$default_timezone;
        }

        public function getLocale(string $locale_id = 'en'): array {
            $file = File::init(
                $this->locales_path . '/' .
                $locale_id . '.' .
                $this->locales_file_ext
            );
            if (!$file->exists()) {
                return [];
            }
            return $file->parseIni();
        }

    }
