<?php
/**
 * Table Definition for forgot
 */
require_once 'DB/DataObject.php';

class arc_Forgot extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'forgot';              // table name
    public $id;                              // int(4)  primary_key not_null
    public $id_users;                        // int(4)   not_null
    public $username;                        // text   not_null
    public $question;                        // text   not_null
    public $answer;                          // text   not_null
    public $hint;                            // text   not_null
    public $lastreq;                         // text   not_null
    public $lastreq_ip;                      // text   not_null
    public $active;                          // text   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('arc_Forgot',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
