<?php
/**
 * Table Definition for invitations
 */
require_once 'DB/DataObject.php';

class arc_Invitations extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'invitations';           // table name
    public $id;                              // int(4)  primary_key not_null
    public $id_users;                        // int(4)   not_null
    public $receipient;                      // text   not_null
    public $time;                            // text   not_null
    public $id_invhash;                      // text   not_null
    public $id_active;                       // text   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('arc_Invitations',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
