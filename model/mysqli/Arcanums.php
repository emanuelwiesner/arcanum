<?php
/**
 * Table Definition for arcanums
 */
require_once 'DB/DataObject.php';

class arc_Arcanums extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'arcanums';            // table name
    public $id;                             // int(4) primary_key not_null
    public $id_portals;                     // int(4) not_null
    public $portal_login;                   // text not_null
    public $portal_pass;                    // text not_null
    public $created;                        // text not_null
    public $active;                         // text
    public $remember;                       // text

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
