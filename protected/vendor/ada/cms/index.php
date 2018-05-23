<?php
    /**
    * @package   project/cms
    * @version   1.0.0 22.05.2018
    * @author    author
    * @copyright copyright
    * @license   Licensed under the Apache License, Version 2.0
    */

    namespace Ada\Core;

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'includes/autoload.php';

    //queries builder
    //
    //    public function where
    //C:\OSPanel\domains\laravel\vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php
    //
    //
    //
    //https://www.w3schools.com/sql/sql_where.asp
    //https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/query-builder.html#the-querybuilder
    //C:\OSPanel\domains\jobfood\libraries\joomla\database\query.php
    //C:\OSPanel\domains\laravel\vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php
    //Sql class
    //Str, Arr and Obj classes

    Db::add([
        'name'   => 'project',
        'prefix' => 'pj_'
    ]);

    Db::add([
        'driver' => 'pgsql',
        'name'   => 'postgres',
        'prefix' => 'pj_',
        'user'   => 'postgres'
    ]);



    exit(var_dump(

        Db::init()->getQuery()
            ->select()
            ->from('users', 'u')
            ->whereNotNull('u.id')
            ->fetchRows()


    ));











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