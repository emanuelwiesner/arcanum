<?php
/**
 * Table Definition for files
 */
require_once 'DB/DataObject.php';

class arc_Files extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'files';               // table name
    public $id;                             // int(4) primary_key not_null
    public $id_categories;                  // int(4) not_null
    public $name;                           // text not_null
    public $size;                           // text not_null
    public $type;                           // text not_null
    public $comment;                        // text not_null
    public $date;                           // text not_null
    public $file;                           // longblob not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
