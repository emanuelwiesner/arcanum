<?php 
class howitworks extends arcanum {
	
	public function __construct(){
		parent::__construct();
		
		$users = DB_DataObject::factory('users');
                define('REGISTERED_USERS', $users->find());

	}
	
}
?>
