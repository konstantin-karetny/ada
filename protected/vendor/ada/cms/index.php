<?php
    /**
    * @package   project/cms
    * @version   1.0.0 06.07.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    //C:\OSPanel\domains\project\protected\vendor\ada\core\input\input.php line 95 ................

    require_once 'includes/autoload.php';

    Db::add([
        'name'   => 'project',
        'prefix' => 'pj_'
    ]);

    Db::add([
        'driver' => 'pgsql',
        'name'   => 'postgres',
        'prefix' => 'pj_',
        'user'   => 'postgres'
    ]);


    //Input\Session::preset(['handler' => new Input\Session\Handlers\Db(Db::init()->getTable('session'))]);



    exit(var_dump(

        Input\Cookie::set('kkkkkk')
        //Input\Cookie::drop('kkkkkk'),Input\Cookie::getStorage()

    ));


    if ($_POST) {
        exit(var_dump( Input\Files::get('fileinp') ));
    }

?>
<form action="" enctype="multipart/form-data" method="POST">
    <input name="txt[a]" value="txt value a">
    <input name="txt[b]" value="txt value b">
    <input name="txt[c]" value="txt value c">
    <input type="file" name="fileinp[]" multiple>
    <button>Submit</button>
</form>



<?php
    die;