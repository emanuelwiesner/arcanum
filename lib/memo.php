<?php 
class memo extends arcanum {
	
	public function __construct(){
		parent::__construct();
	}

	public function show (){
		$memo = DB_DataObject::factory('memos');
		$memo->id_users = $this->id;

		$found_count =  $memo->find();
		if ($found_count){
			while($memo->fetch()){
				$this->data[$memo->id] = clone($memo);
			}
		}
	}

	public function add (){
    	if ($this->request['title'] != ""){
			$memos = DB_DataObject::factory('memos');
			$memos->id_users = $this->id;
			
			$this->request['updated'] = TIME;

			$save_memos = $this->arc_encrypt_input($memos, $this->request);
			$save_memos->insert();

			$this->session_msg(e('memo_created') ." [" . $this->request['title'] . "]");
		} else {
			$this->session_msg(e('no_memo_wihtout_name'));
		}
		
		redirect('memo');
	}


	public function update (){
		$id = $this->request['id_memo'];

		$this->check_permissons('memos', $id);

		$memos = DB_DataObject::factory('memos');
		$memos->get($id);

		$this->request['updated'] = TIME;

		$memos_updated = $this->arc_encrypt_input(clone($memos), $this->request);
		$memos_updated->update();

		$this->useview = FALSE;
		$this->content = 1;

		$title = $this->arc_decrypt_output($memos->title);
		$this->user_log(e('memo_changed')." [".$title."]");
	}


	public function del () {
		$this->useview = FALSE;

		$id = $this->request['id_memo'];

		$this->check_permissons('memos', $id);

		$memos = DB_DataObject::factory('memos');
		$memos->get($id);
		
		$title = $this->arc_decrypt_output($memos->title);
		$memos->delete();

		$this->user_log(e('memo_deleted')." [".$title."]");
		$this->content = 1;
	}

}
