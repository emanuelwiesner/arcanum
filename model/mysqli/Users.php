<?php
/**
 * Table Definition for users
 */
require_once 'DB/DataObject.php';

class arc_Users extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'users';               // table name
    public $id;                              // int(4)  primary_key not_null
    public $id_invited;                      // int(4)   not_null
    public $login;                           // text   not_null
    public $password;                        // text   not_null
    public $colour;                          // text   not_null
    public $lastlogin;                       // text  
    public $lastip;                          // text  
    public $lastbrowser;                     // text  
    public $lastupdated;                     // text  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('arc_Users',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
