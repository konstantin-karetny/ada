<?php
    /**
    * @package   ada/cms
    * @version   1.0.0 01.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    require_once 'includes/autoload.php';



    exit(var_dump( Input::get('HTTP_ACCEPT_LANGUAGE', 'bool', '', 'server') ));






    $app = \Ada\Framework\App::init();






    $app->setInterfaces(['zz', 'qq']);

    exit(var_dump( $app ));













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

    if($_POST || $_FILES) Url::redirect(Url::current());

    echo $app->get_body();
