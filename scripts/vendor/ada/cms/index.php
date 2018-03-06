<?php
    /**
    * @package   ada/cms
    * @version   1.0.0 18.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    require_once 'includes/autoload.php';

    //SESSION
    //Signature
    //db handler
    //C:\OSPanel\domains\joomla\libraries\joomla\session\storage\database.php
    //
    //folder
    //db

    exit(var_dump( Client::init(), Client::init()->getSignature() ));


    $session = Session::init();
    //$handler = new SessionHandlerDb;
    //$session->setHandler($handler);
    //Session::set('var', 'val');
    //$session->start();

    exit(var_dump( $session->start(), $_SESSION ));



















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