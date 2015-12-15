<?php
/**
 * Table Definition for settings
 */
require_once 'DB/DataObject.php';

class arc_Settings extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'settings';            // table name
    public $id;                             // int(4) primary_key not_null
    public $id_users;                       // int(4) not_null
    public $use_autolinkgen;                // text
    public $expand_memos;                   // text
    public $session_lifetime;               // text
    public $start_module;                   // text
    public $hide_desc;                      // text
    public $hide_comment;                   // text
    public $use_forgot;                     // text
    public $lang;                           // text
    public $patternlock;                    // text
    public $arc_pass_notify_interval;       // text

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
