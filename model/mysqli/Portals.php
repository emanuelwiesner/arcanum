<?php
/**
 * Table Definition for portals
 */
require_once 'DB/DataObject.php';

class arc_Portals extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'portals';             // table name
    public $id;                             // int(4) primary_key not_null
    public $id_categories;                  // int(4) not_null
    public $name;                           // text not_null
    public $desc;                           // text
    public $common_used;                    // text
    public $link;                           // text

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
