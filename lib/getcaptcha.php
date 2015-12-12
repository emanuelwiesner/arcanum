<?php 
class getcaptcha extends arcanum {
	
	public function __construct(){
		parent::__construct();
	}

	public function check () {
		$this->useview = $this->setlayout = FALSE;
		return ("HUHU");
	}
	
}
?>
