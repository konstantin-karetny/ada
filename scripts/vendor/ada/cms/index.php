<?php
    /**
    * @package   ada/cms
    * @version   1.0.0 14.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    require_once 'includes/autoload.php';

    //db
    //db session handler
    //C:\OSPanel\domains\joomla\libraries\joomla\session\storage\database.php


    class Test {
        private
            $id,
            $name;
    }


    $db = Db::init(
        1,
        [
           'name'   => 'ada',
           'prefix' => 'ada_'
        ]
    );


    $table  = $db->getTable('test');
    $column = $table->getColumn('state');
    $column->setLength(255);
    $column->setType('varchar');
    $column->setCollation('utf8_general_ci');
    $column->setDefaultValue('defffffff');

    exit(var_dump(
        $column->create($table->getColumn('id')),
        $db->debugDumpParams()
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