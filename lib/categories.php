<?php 
class categories extends arcanum {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function show () {

		//SHOW Categogries
		$categories = DB_DataObject::factory('categories');
		$categories->id_users = $this->id;
		if ($categories->find()){

			while($categories->fetch())
				$this->data[$categories->id] = clone($categories);			
		}
		
		
		$settings = DB_DataObject::factory('settings');
		$settings->id_users = $this->id;
		$settings->find(TRUE);
		
		$this->content['use_autolinkgen'] = FALSE;
		if ( $settings->use_autolinkgen == $this->arc_encrypt_input('yes') )
			$this->content['use_autolinkgen'] = TRUE;
	}
	
	
	public function favs (){
		$this->show();
		
		$user_encrypted_yes = $this->arc_encrypt_input("yes");

		if (is_array($this->data)) {
			foreach ($this->data as $id => $cat){
				$fav = FALSE;
			
				$portals = DB_DataObject::factory('portals');
				$portals->id_categories = $id;
				$portals->common_used = $user_encrypted_yes;
			
				if ($portals->find() == 0)
					unset($this->data[$id]);
			}
		}
	}
	
	
	public function add (){
		
		if ($this->request['category'] != ""){
			$categories = DB_DataObject::factory('categories');
			$categories->id_users = $this->id;
			
			$save_category = $this->arc_encrypt_input($categories, $this->request);
			$save_category->insert();
			
			$this->session_msg(e('category_created') ." [" . $this->request['category'] . "]");
		} else {
			$this->session_msg(e('no_cat_wihtout_name'));
		}
		
		redirect('categories');
	}
	
	public function update (){
		$id = $this->request['id_cat'];
	
		$this->check_permissons('categories', $id);
	
		$categories = DB_DataObject::factory('categories');
		$categories->get($id);
	
		$categories_updated = $this->arc_encrypt_input(clone($categories), $this->request);
		$categories_updated->update();				
	
		$this->useview = FALSE;
		$this->content = 1;

		$cat_name = $this->arc_decrypt_output($categories->category);
		$this->user_log(e('category_changed')." [".$cat_name."]");
		//redirect('categories');
	}
	
	public function del () {
		$this->useview = FALSE;
		
		$id = $this->request['id_cat'];
		
		$this->check_permissons('categories', $id);
		
		$files = DB_DataObject::factory('files');
		$files->id_categories = $id;
		
		$categories = DB_DataObject::factory('categories');
		$categories->id_users = $this->id;
		$categories->id = $id;		
		
		$categories->find(TRUE);
		$cat_name = $this->arc_decrypt_output($categories->category);
		
		if ($files->find() == 0){

			$categories->delete();
			
			$this->content = 1;
		
		} else {
			$this->content = 2;
		}
		
		$this->user_log(e('category_deleted')." [".$cat_name."]");
	}
	
	
}
?>
