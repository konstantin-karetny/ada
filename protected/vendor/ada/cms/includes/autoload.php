<?php
    /**
    * @package   project/cms
    * @version   1.0.0 22.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    set_include_path(
        implode(
            PATH_SEPARATOR,
            [
                get_include_path(),
                $path_root . '/protected/vendor'
            ]
        )
    );
    spl_autoload_extensions('.php');
    spl_autoload_register();
    require_once $path_root . '/protected/vendor/autoload.php';
