<?php
/**
 * Table Definition for jail
 */
require_once 'DB/DataObject.php';

class arc_Jail extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'jail';                // table name
    public $id;                             // int(4) primary_key not_null
    public $ip;                             // text not_null
    public $tries;                          // int(4) not_null
    public $time;                           // int(4) not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
