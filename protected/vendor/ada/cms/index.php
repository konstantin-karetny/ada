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

    //queries builder ?
    //C:\OSPanel\domains\jobfood\libraries\joomla\database\query.php
    //C:\OSPanel\domains\laravel\vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php
    //https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/query-builder.html#the-querybuilder
    //Sql class

    //Str, Arr and Obj classes

    Db::add([
        'name'   => 'project',
        'prefix' => 'pj_'
    ]);

    exit(var_dump( Db::init()->exec(Session::init()) ));

    Db::add([
        'driver' => 'pgsql',
        'name'   => 'postgres',
        'prefix' => 'pj_',
        'user'   => 'postgres'
    ]);

    $db    = Db::init();
    $query = $db->getQuery();

    exit(var_dump( $query->select(['id'])->from('session')->where('id', 'NOT IN', ['zz' ,'sad'])->where('id', '!=', 'zz')->toString() ));











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