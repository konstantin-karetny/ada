<?php
    /**
    * @package   project/core
    * @version   1.0.0 10.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    class App extends Proto {

        protected static
            $default_controller = '',
            $default_side       = 'site',
            $default_task       = '',
            $default_unit       = '',
            $inited             = false,
            $sides              = [
                'site',
                'admin'
            ],
            $units_namespace    = '\Ada\CMS\Units';

        protected
            $args               = [],
            $controller         = '',
            $side               = '',
            $task               = '',
            $unit               = '';

        public static function getDefaultController(): string {
            return static::$default_controller;
        }

        public static function getDefaultSide(): string {
            return static::$default_side;
        }

        public static function getDefaultTask(): string {
            return static::$default_task;
        }

        public static function getDefaultUnit(): string {
            return static::$default_unit;
        }

        public static function getSides(): array {
            return static::$sides;
        }

        public static function getUnitsNamespace(): string {
            return static::$units_namespace;
        }

        public static function init(\Ada\Core\Url $url): \Ada\Core\App {
            return new static($url);
        }

        public static function isInited(): bool {
            return static::$inited;
        }

        public static function preset(array $params): bool {
            if (static::$inited) {
                return false;
            }
            foreach ($params as $k => $v) {
                $k = Clean::cmd($k);
                switch ($k) {
                    case 'default_controller':
                    case 'default_side':
                    case 'default_task':
                    case 'default_unit':
                        static::$$k              = Clean::cmd($v);
                        break;
                    case 'sides':
                        static::$sides           = Clean::values(
                            Types::set($v, 'arr', false),
                            'cmd'
                        );
                        break;
                    case 'units_namespace':
                        static::$units_namespace = Clean::classname($v);
                        break;
                }
            }
            return true;
        }

        protected function __construct(\Ada\Core\Url $url) {
            $defaults = [];
            foreach ([
                'controller',
                'side',
                'task',
                'unit'
            ] as $prop) {
                $defaults[$prop] = static::{'getDefault' . ucfirst($prop)}();
            }
            foreach (array_merge($defaults, $url->getVars()) as $k => $v) {
                switch ($k) {
                    case key_exists($k, $defaults):
                        $this->$k = Clean::cmd($v);
                        break;
                    default:
                        $this->args[$k] = Types::set($url->getVar($k, 'str'));
                }
            }
            $side = Clean::cmd(
                Type\Arr::init(explode('/', $url->getPath()))->first()
            );
            if (in_array($side, static::getSides())) {
                $this->side = $side;
            }
        }

        public function exec(): bool {
            $controller_classname = $this->getControllerClassname();
            if (!class_exists($controller_classname)) {
                
            }

            exit(var_dump(class_exists($controller_classname) ));

            $units_dir = Fs\Dir::init(static::getUnitsDir());
            if (!$units_dir->exists()) {
                throw new Exception(
                    'No units directory \'' . $units_dir->getPath() . '\'',
                    1
                );
            }
            $unit_dir = Fs\Dir::init(
                $units_dir->getPath() . '/' . $this->getUnit()
            );
            if (!$unit_dir->exists()) {
                throw new Exception(
                    'Unknown unit \'' . $this->getUnit() . '\'',
                    2
                );
            }


            $controller_name = '\Ada\CMS\Units\Articles\Controllers\Item';


            exit(var_dump( \Ada\CMS\Units\Articles\Controllers\Item::init() ));
        }

        public function getArgs(): array {
            return $this->args;
        }

        public function getController(): string {
            return $this->controller;
        }

        public function getControllerClassname(): string {
            return implode(
                '\\',
                [
                    static::getUnitsNamespace(),
                    ucfirst($this->getUnit()),
                    'Controllers',
                    ucfirst($this->getController())
                ]
            );
        }

        public function getSide(): string {
            return $this->side;
        }

        public function getTask(): string {
            return $this->task;
        }

        public function getUnit(): string {
            return $this->unit;
        }

    }
