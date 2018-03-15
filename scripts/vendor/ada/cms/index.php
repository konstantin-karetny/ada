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

    exit(var_dump(
        $db->fetchRow('SELECT * FROM ' . $db->t('test'), \PDO::FETCH_BOTH),
        $db->setFetchMode(\PDO::FETCH_CLASS, '\Ada\Core\Test'),
        $db->fetchRow('SELECT * FROM ' . $db->t('test') . ' WHERE ' . $db->q('name') . ' = ' . $db->esc('Test 1'), \PDO::FETCH_CLASS),
        $db->debugDumpParams(),
        $db->getPdoParam(\PDO::ATTR_DEFAULT_FETCH_MODE)
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