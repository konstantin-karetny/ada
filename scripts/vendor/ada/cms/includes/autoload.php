<?php
    /**
    * @package    package_cms
    * @version    1.0.0 11.10.2016
    * @copyright  copyright
    * @license    Licensed under the Apache License, Version 2.0
    */

    set_include_path(
        implode(
            PATH_SEPARATOR,
            [
                get_include_path(),
                $path_root . '/scripts/vendor'
            ]
        )
    );
    spl_autoload_extensions('.php');
    spl_autoload_register();
    require_once $path_root . '/scripts/vendor/autoload.php';
