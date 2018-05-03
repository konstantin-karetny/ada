<?php
    /**
    * @package   project/cms
    * @version   1.0.0 03.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'includes/autoload.php';

    //table  setColumn(array $params) / setColumns(array $params) ?
    //table  get constraints
    //column delete only column from constraint, not all column in constraint
    //column types and args_qtys
    //column attributes?
    //db session handler
    //C:\OSPanel\domains\joomla\libraries\joomla\session\storage\database.php
    //Str, Arr and Obj classes

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


    $db = Db::init(0);
    $t  = $db->getTable('test2');

    //$t->getColumns(true);

    exit(var_dump( $t->getConstraints() ));

    $c = $t->getColumn('name');
    $c->setUniqueKey(false);
    exit(var_dump( $c->save() ));

    $t->setName('test3');
    $t->setColumns(
        [
            [
                'name'              => 'id',
                'primary_key'       => true,
                'unique_key'        => true,
                'is_auto_increment' => true
            ],
            [
                'name' => 'zz'
            ]
        ]
    );

    exit(var_dump( $t ));

    $c = $t->getColumn('price');

    $c->setUniqueKey(false);

    //setTypeArgs for numeric PgSQL

    exit(var_dump( $c->save() ));

    $c->setName('price');
    $c->setType('decimal');
    $c->setTypeArgs(10, 5);
    $c->setAfter('id');
    $c->setIsNullable(false);
    $c->setUniqueKey(true);


    exit(var_dump( $c->save() ));



    exit(var_dump(

        Db::init(0)->createTable(
            [
                'name' => 'test2',
                'columns' => [
                    [
                        'name'              => 'id',
                        'primary_key'       => true,
                        'unique_key'        => true,
                        'is_auto_increment' => true
                    ],
                    [
                        'name'              => 'name',
                        'type'              => 'varchar',
                        'length'            => 255,
                        'unique_key'        => true
                    ],
                    [
                        'name'              => 'text',
                        'type'              => 'text'
                    ]
                ]
            ]
        )

    ));

    $t1 = Db::init(0)->getTable('test');
    //$t1->getColumns();
    $t2 = Db::init(1)->getTable('test');
    //$t2->getColumns();


    exit(var_dump( Db::init()->createTable([
        'name'    => 'test',
        'columns' => [
            [
                'name'              => 'id',
                'type'              => 'int',
                'is_primary_key'    => true,
                'is_auto_increment' => true
            ]
        ]
    ]) ));


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