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


    if ($_FILES) {



        exit(var_dump( Input::getFiles('fileinp') ));




    }

?>
<form action="" enctype="multipart/form-data" method="POST">
    <input name="txt" value="txt value">
    <input type="file" name="fileinp[]" multiple>
    <button>Submit</button>
</form>



<?php
    die;



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
