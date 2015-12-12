<?php 
class login extends arcanum {
	
	public function __construct(){
		parent::__construct();

	}

	public function show () {
		redirect('dashboard');
	}

	
	public function logout(){
		$this->content = e('default_goodbye');
		$log = (isset($this->request['msg'])) ? unserialize($this->request['msg']) : e("logout");
		$this->user_log($log);

		$this->kill();
		sleep(1);
	}
	
}
?>
