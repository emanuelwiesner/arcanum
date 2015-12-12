<?php 
class timer extends arcanum {

	public $content;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function show () {

		$this->useview = $this->setlayout = FALSE;
		$this->content = $this->time_left_display();

	}

	public function regenerate () {

		$this->show();

	}
}
?>
