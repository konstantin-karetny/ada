<?php
    /**
    * @package   project/core
    * @version   1.0.0 23.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class DateTime extends \DateTime {

        protected static
            $default_format        = 'Y-m-d H:i:s',
            $default_timezone      = null,
            $default_timezone_name = '',
            $inited                = false;

        protected
            $locales_ext           = 'ini',
            $locales_path          = __DIR__ . '/datetime/locales';

        public static function getDefaultFormat(): string {
            return static::$default_format;
        }

        public static function getDefaultTimezone(): \Ada\Core\DateTimeZone {
            return
                (
                    static::$default_timezone &&
                    static::$default_timezone->getName() == static::getDefaultTimezoneName()
                )
                    ? static::$default_timezone
                    : static::$default_timezone = DateTimeZone::init(
                        static::getDefaultTimezoneName()
                    );
        }

        public static function getDefaultTimezoneName(): string {
            return
                static::$default_timezone_name
                    ? static::$default_timezone_name
                    : static::$default_timezone_name = date_default_timezone_get();
        }

        public static function init(
            string $time          = 'now',
            string $timezone_name = ''
        ): \Ada\Core\DateTime {
            return new static(...func_get_args());
        }

        public static function setDefaultFormat(string $default_format): bool {
            if (static::$inited) {
                return false;
            }
            static::$default_format = $default_format;
            return true;
        }

        public static function setDefaultTimezoneName(
            string $default_timezone_name
        ): bool {
            if (static::$inited) {
                return false;
            }
            static::$default_timezone      = DateTimeZone::init($default_timezone_name);
            static::$default_timezone_name = static::$default_timezone->getName();
            date_default_timezone_set(static::$default_timezone_name);
            return true;
        }

        public function __construct(
            string $time          = 'now',
            string $timezone_name = ''
        ) {
            parent::__construct(
                $time,
                $timezone_name
                    ? DateTimeZone::init($timezone_name)
                    : $this->getDefaultTimezone()
            );
            $this->locales_path = Clean::path($this->locales_path);
            static::$inited     = true;
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
