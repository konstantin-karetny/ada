<?php
    /**
    * @package   project/cms
    * @version   1.0.0 13.04.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'includes/autoload.php';

    //column drop default
    //column delete
    //column rename
	//https://www.1keydata.com/sql/sql-alter-table.html
	//unsigned and etc.
    //db
    //db session handler
    //Str, Arr and Obj classes
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

    $t = Db::init(0)->getTable('test');

    exit(var_dump( $t->getColumns(true) ));

    exit(var_dump(

        Db::init(1)->createTable(
            [
                //'name' => 'test2',
                //'schema' => 'information_schema',
                'columns' => [
                    [
                        'name'              => 'id',
                        'is_primary_key'    => true,
                        'is_auto_increment' => true
                    ],
                    [
                        'name'              => 'name',
                        'iis_unique_key'    => true
                    ]
                ]
            ]
        )

    ));

    $t1 = Db::init(0)->getTable('test');
    //$t1->getColumns();
    $t2 = Db::init(1)->getTable('test');
    //$t2->getColumns();


    $db  = Db::init(0);
    $t22 = $db->getTable('test2');

    $column = $t22->getColumn('state');
    $column->setLength(255);
    $column->setType('varchar');
    $column->setDefaultValue('false');
    $column->setIsUniqueKey(true);

    exit(var_dump(
        $column,
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