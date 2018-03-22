<?php
    /**
    * @package   ada/cms
    * @version   1.0.0 22.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    require_once 'includes/autoload.php';

    //db
    //db session handler
    //C:\OSPanel\domains\joomla\libraries\joomla\session\storage\database.php




    Db::add([
        'name'   => 'joom',
        'prefix' => 'joom_'
    ]);
    $db = Db::init();



    $table = $db->getTable('test');
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