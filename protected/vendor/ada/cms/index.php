<?php
    /**
    * @package   project/cms
    * @version   1.0.0 04.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'includes/autoload.php';

    //db session handler
    //C:\OSPanel\domains\joomla\libraries\joomla\session\storage\database.php
    //public static function preset
    //Sql class
    //Str, Arr and Obj classes

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


    $handler = new Session\Handlers\Db(Db::init()->getTable('session'));
    Session::preset([], $handler);

    //Session::set('var', 'val');
    //Session::drop('var');

    exit(var_dump( Session::init() ));











    if ($_POST) {
        exit(var_dump( Files::get('fileinp') ));
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