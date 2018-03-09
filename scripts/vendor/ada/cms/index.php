<?php
    /**
    * @package   ada/cms
    * @version   1.0.0 09.03.2018
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
        true,
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
            'u.id',
            'u.email'           => 'email',
            'u.password',
            'u.create_datetime' => 'cd'
        ])       . '
        FROM '   . $db->t('users', 'u')            . '
        WHERE '  . $db->q('id')              . ' > ' . $db->esc(0) . '
        AND '    . $db->q('create_datetime') . ' > ' . $db->esc('2002-01-01 00:00:00') . '
        AND '    . $db->q('password')        . ' LIKE ' . $db->esc('password%') . '
        OR '     . $db->q('password')        . ' LIKE ' . $db->esc('pa:    :ssword%') . '
    ';


    exit(var_dump( $db->selectRows($query), $db ));



















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