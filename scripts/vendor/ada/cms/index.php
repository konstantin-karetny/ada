<?php
    /**
    * @package   ada/cms
    * @version   1.0.0 02.10.2017
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada;

    require_once $path_root . '/scripts/vendor/ada/cms/includes/autoload.php';


    exit(var_dump( Tools\Uri::getInst() ));

    $app = Framework\App::getInst();





    exit(var_dump( $app ));










    //define('ADA_PATH_ROOT', __DIR__);
    //require_once ADA_PATH_ROOT    . '/scripts/includes/defines.php';
    //require_once ADA_PATH_SCRIPTS . '/includes/autoload.php';
    require_once 'scripts/includes/autoload.php';


    exit(var_dump( Lib\Path::getInst(546454) ));


    Lib\Config::getInst();

    Lib\Db::getInst([
        'name' => 'ada',
        'user' => 'root'
    ]);

    exit(var_dump( Lib\Singleton::getInstances() ));











    Error::getinst()->set_reporting();
    Session::prolong_lifetime();

    $app = Application::getinst();
    $app->execute();

    if($_POST || $_FILES) Uri::redirect(Uri::current());

    echo $app->get_body();
