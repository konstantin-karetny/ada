<?php
    /**
    * @package   ada/cms
    * @version   1.0.0 12.03.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    require_once 'includes/autoload.php';

    //db
    //db session handler
    //C:\OSPanel\domains\joomla\libraries\joomla\session\storage\database.php
    //
    //folder



    $db = Db::init(
        1,
        [
           'name'   => 'ada',
           'prefix' => 'ada_'
        ]
    );




    //$handler = new SessionHandlerDb;
    //$session->setHandler($handler);
    //Session::set('var', 'val');
    //$session->start();

    $query = '
        SELECT ' . $db->qs([
            't.id',
            't.name'           => 'name',
            't.text'
        ])       . '
        FROM '   . $db->t('test', 't')            . '
        WHERE '  . $db->q('id')              . ' > ' . $db->esc(0) . '
        AND '    . $db->q('name')        . ' LIKE ' . $db->esc('Test%') . '
        AND '    . $db->q('text')        . ' LIKE ' . $db->esc('Lorem 1%') . '
    ';

    $query = '
        SELECT ' . $db->qs([
            't.id',
            't.name'           => 'name',
            't.text'
        ])       . '
        FROM '   . $db->t('test', 't')            . '
        WHERE '  . $db->q('id')              . ' > ' . $db->esc(0) . '
        AND '    . $db->q('name')        . ' LIKE ' . $db->esc('Test%') . '
        OR '    . $db->q('text')        . ' LIKE ' . $db->esc('Lorem 1%') . '
    ';

    $data = [
        'name' => 'Test 4',
        'text' => 'Lorem 4 ipsum dolor sit amet'
    ];


    exit(var_dump( $db->update('test', $data, $db->where('id', '=', 4)) ));

    //exit(var_dump( $db->insert('test', $data) ));
    exit(var_dump( $db->delete('test', $db->q('id') . ' = ' . $db->esc(4)) ));
    //exit(var_dump( $db->update('test', $data, $db->q('id') . ' = ' . $db->esc(4)) ));






















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