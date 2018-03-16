<?php
    /**
    * @package   ada/core
    * @version   1.0.0 16.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class DateTime extends \DateTime {

        protected static
            $default_format   = '',
            $default_timezone = null;

        protected
            $locales_ext      = 'ini',
            $locales_path     = __DIR__ . '/datetime/locales';


        public static function getDefaultFormat(): string {
            return static::$default_format;
        }

        public static function getDefaultTimezone(): DateTimeZone {
            return static::$default_timezone;
        }

        public static function init(
            string $time        = 'now',
            string $timezone_id = ''
        ): self {
            return new static(...func_get_args());
        }

        public static function setDefaultFormat(string $format) {
            if (!static::$default_format) {
                static::$default_format = $format;
            }
        }

        public static function setDefaultTimezone(string $timezone_id) {
            if (!static::$default_timezone) {
                static::$default_timezone = DateTimeZone::init($timezone_id);
                date_default_timezone_set(static::$default_timezone->getName());
            }
        }

        public function __construct(
            string $time        = 'now',
            string $timezone_id = ''
        ) {
            if (!static::$default_format) {
                static::setDefaultFormat('Y-m-d H:i:s');
            }
            if (!static::$default_timezone) {
                static::setDefaultTimezone(date_default_timezone_get());
            }
            parent::__construct(
                $time,
                $timezone_id
                    ? DateTimeZone::init($timezone_id)
                    : static::getDefaultTimezone()
            );
            $this->locales_path = Clean::path($this->locales_path);
        }

        public function format($format = '', string $locale_id = 'en'): string {
            $format = $format ? Clean::cmd($format) : static::getDefaultFormat();
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

        public function getLocale(string $locale_id = 'en'): array {
            $file = File::init(
                $this->locales_path . '/' .
                $locale_id . '.' .
                $this->locales_ext
            );
            if (!$file->exists()) {
                return [];
            }
            return $file->parseIni();
        }

    }
