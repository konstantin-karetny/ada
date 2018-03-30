<?php
    /**
    * @package   project/cms
    * @version   1.0.0 29.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'includes/autoload.php';

    //cache for Client, DateTime and other
    //db
    //db session handler
    //Str Arr Obj classes
    //C:\OSPanel\domains\joomla\libraries\joomla\session\storage\database.php



    Db::add([
        'name'   => 'project',
        'prefix' => 'pj_'
    ]);
    Db::add([
        'driver' => 'pgsql',
        'name'   => 'postgres',
        'prefix' => 'pj_',
        'user'   => 'postgres',
    ]);


    exit(var_dump( Db::init(1)->getTable('test') ));


    $t1 = Db::init(0)->getTable('test');
    //$t1->getColumns();
    $t2 = Db::init(1)->getTable('test');
    $t2->getColumns();


    $t11 = Db::init(1)->getTable('test2');

    exit(var_dump( $t1 ));



    $table = $db->getTable('ids');
    $table->getColumns();
    exit(var_dump( $table ));





    $column = $table->getColumn('state');
    $column->setLength(255);
    $column->setType('varchar');
    $column->setCollation('utf8_general_ci');
    $column->setDefaultValue('defffffff');

    exit(var_dump(
        $column->create($table->getColumn('id')),
        $db->debugInfo()
    ));


    //$handler = new SessionHandlerDb;
    //$session->setHandler($handler);
    //Session::set('var', 'val');
    //$session->start();






















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