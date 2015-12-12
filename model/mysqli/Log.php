<?php
/**
 * Table Definition for log
 */
require_once 'DB/DataObject.php';

class arc_Log extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'log';                 // table name
    public $id;                              // int(4)  primary_key not_null
    public $id_users;                        // int(4)   not_null
    public $time;                            // text  
    public $ip;                              // text  
    public $log;                             // text  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('arc_Log',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
