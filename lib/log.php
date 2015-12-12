<?php 
class log extends arcanum {

	public function __construct(){
		parent::__construct();
	}

	public function show ($showall = FALSE) {

		$log = DB_DataObject::factory('log');
		$log->id_users = $this->id;
		
		$found_count = 	$log->find();	
		if ($found_count){
			
			if (($showall == FALSE)){
				$log->limit($found_count-$this->log_max_show, $found_count);
				$this->content['logcount'] = $log->count();
			}
			
			while($log->fetch()){			
				$this->data[$log->id] = clone($log);
			}
				
		}
		$this->content['showall'] = $showall;
	}
	
	
	public function delete () {
		
		$log = DB_DataObject::factory('log');
    	$log->id_users = $this->id;
    	$this->user_log(e('log_cleanup', array($log->delete())));
    	
		redirect('log');  			
	}
	
	public function showall (){
		$this->show(TRUE);
	}
}
?>
