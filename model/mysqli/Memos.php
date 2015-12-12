<?php
/**
 * Table Definition for memos
 */
require_once 'DB/DataObject.php';

class arc_Memos extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'memos';               // table name
    public $id;                              // int(4)  primary_key not_null
    public $id_users;                        // int(4)   not_null
    public $title;                           // text   not_null
    public $note;                            // text  
    public $updated;                         // text  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('arc_Memos',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
