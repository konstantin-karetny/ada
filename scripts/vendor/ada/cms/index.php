<?php
    /**
    * @package   ada/cms
    * @version   1.0.0 01.02.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    require_once 'includes/autoload.php';


    //url::redirect

    //folder
    //cookie
    //db
    //session
    //time


    if (empty($_GET['var1'])) {
        exit(var_dump( Url::redirect('http://ada/?var1=1.235&var2=http://ada?var1=1.235&var2=asdf') ));
    }

