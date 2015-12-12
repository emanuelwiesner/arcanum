<?php 
class export extends arcanum {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function show (){
		
		$this->user_log(e('portals_opened_all'));

		//categories
		$categories = DB_DataObject::factory('categories');
		$categories->id_users = $this->id;
		if ($categories->find()){
			while($categories->fetch()){				
				$out_array = $this->arc_decrypt_output(array(clone($categories)));
				$this->content[$categories->id] = $out_array[0];
				$cats[] = $categories->id;
			}
		
		}
	
		if (isset($cats)){	
			foreach ($cats as $cat){
		
				//portals
				$portals = DB_DataObject::factory('portals');
				$portals->id_categories = $cat;
				if ($portals->find()){
					while($portals->fetch()){
						$ports[$portals->id] = $cat;
						$portals_array = $this->arc_decrypt_output(array(clone($portals)));
						$this->content[$cat]['portals'][$portals->id] = $portals_array[0];
					}
					
				}
		
			}
		}
		
		if (isset($ports)){
			foreach ($ports as $port => $cat_id){
				//arcanums
				$arcanums = DB_DataObject::factory('arcanums');
				$arcanums->id_portals = $port;
				$arcanums->active = $this->arc_encrypt_input("y");
				if ($arcanums->find()){
					while($arcanums->fetch()){
						$arcanums_array = $this->arc_decrypt_output(array(clone($arcanums)));
						$this->content[$cat_id]['portals'][$port]['arcanums'][$arcanums->id] = $arcanums_array[0];
					}
						
				}
			}
		}
		
		$this->layout = FALSE;
		
	}
	
}
?>
