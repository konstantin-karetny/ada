<?php
    /**
    * @package   ada/cms
    * @version   1.0.0 05.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    require_once 'includes/autoload.php';

    //Signature
    //Временные метки устаревших сессий
    //db handler
    //session
    //
    //folder
    //time
    //db


    $session = Session::init();
    $session->setIniParam('save_handler', 'db');


    exit(var_dump( $_SESSION, $session->start() ));



















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