<?php
/**
 * Table Definition for categories
 */
require_once 'DB/DataObject.php';

class arc_Categories extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'categories';          // table name
    public $id;                             // int(4) primary_key not_null
    public $id_users;                       // int(4) not_null
    public $category;                       // text not_null
    public $desc;                           // text

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
