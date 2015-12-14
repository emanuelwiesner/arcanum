<?php 
class export extends arcanum {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function importall () {

		$password = $this->request['import_password'];
		$newvalues = $_FILES['thefile']; 
		
		if ($this->imports_only_on_empty_db == TRUE) {
			$count = 0;
			$categories = DB_DataObject::factory('categories');
			$categories->id_users = $this->id;
			$count += $categories->find();
			
			$memos = DB_DataObject::factory('memos');
			$memos->id_users = $this->id;
			$count += $memos->find();
		
			if ($count > 0) {
				$this->session_msg(e('imports_only_on_empty_db'));
				redirect('dashboard');
			}
		}
	
		
		if ($newvalues['error'] != "0") {			
			$this->session_msg(e('upload_problem'));
			redirect('dashboard');
		}
		

		$b64dec = base64_decode(file_get_contents($_FILES['thefile']['tmp_name']));
		if ($b64dec == FALSE) {
			$this->session_msg(e('problem_with_import_dec64'));
			redirect('dashboard');
		} else { 
			try {
				$all = unserialize($this->arc_decrypt($b64dec, $password));
			} catch (arcException $e) {
				$this->session_msg(e('problem_with_import_dec'));			
				redirect('dashboard');
			}
		}	
		unset($b64dec);

			foreach ($all['data'] as $cats) {		
				
				$categories = DB_DataObject::factory('categories');
				$categories->id_users = $this->id;
				
				$save_category = $this->arc_encrypt_input($categories, $cats['category'][0]);
				$new_cat_id = $save_category->insert();
				
				foreach ($cats['portals'] as $portal) {
					
						$portals = DB_DataObject::factory('portals');
						$portals->id_categories = $new_cat_id;
						
						$save_portals = $this->arc_encrypt_input($portals, $portal['portal'][0]);
						$new_portal_id = $save_portals->insert();
						
						foreach ($portal['portal']['arcanums'] as $arcanum) {
							
							$arcanums = DB_DataObject::factory('arcanums');
							$arcanums->id_portals = $new_portal_id;
						
							$save_arcanums = $this->arc_encrypt_input($arcanums, $arcanum[0]);
							$save_arcanums->insert();
						}
				
				
				}			
				
				foreach ($cats['files'] as $fils) {
					$files = DB_DataObject::factory('files');
					$files->id_categories = $new_cat_id;
				
					$save_files = $this->arc_encrypt_input($files, $fils[0]);
					$save_files->insert();				
				}
			}
			

			foreach ($all['memos'] as $oldid => $memo) {
				$memos = DB_DataObject::factory('memos');
				$memos->id_users = $this->id;
				
				$save_memos = $this->arc_encrypt_input($memos, $memo[0]);
				$save_memos->insert();				
			}
			
			unlink($_FILES['thefile']['tmp_name']);
			$this->session_msg(e('import_success'));

		$this->user_log(e('import_success'));
		logit("User ".$this->id." imported data.");	
		redirect('dashboard');		
	}	
	
	
	public function exportall () {  
		if ($this->request['export_password1'] != $this->request['export_password2']) {
			$this->session_msg(e('password_not_match'));
			redirect('export');
		}
	
		$categories = DB_DataObject::factory('categories');
		$categories->id_users = $this->id;

				$all = array();

				
				if ($categories->find()){					
					while($categories->fetch()){
						$cats[] = $categories->id;
						$all['data'][$categories->id]['category'] = $this->arc_decrypt_output(array($categories));
					}
				}

				foreach ($cats as $cat){
					$files = DB_DataObject::factory('files');
					$files->id_categories = $cat;
					
					$countfiles = $files->find();
					if ($countfiles){
						while($files->fetch()){							
							$all['data'][$cat]['files'][$files->id] = $this->arc_decrypt_output(array($files));
						}							
					}
					unset($files);#Perf

					
					//3///// --->  STEP: portals
					$portals = DB_DataObject::factory('portals');
					$portals->id_categories = $cat;
					if ($portals->find()){
						while($portals->fetch()){						
							$all['data'][$cat]['portals'][$portals->id]['portal'] = $this->arc_decrypt_output(array($portals));
							
							$arcanums = DB_DataObject::factory('arcanums');
							$arcanums->id_portals = $portals->id;
							if ($arcanums->find()){
								while($arcanums->fetch()){
									$all['data'][$cat]['portals'][$portals->id]['portal']['arcanums'][] = $this->arc_decrypt_output(array($arcanums));				
								}
								
							}							
						}							
					}						
				}
				
				
			
				
				//5.75 ---> STEP: INVITATIONS
				$invs = DB_DataObject::factory('invitations');
				$invs->id_users = $this->id;
				if ($invs->find()){
					while($invs->fetch()){
						$all['invitations'][] = $this->arc_decrypt_output(array($invs));
					}
				
				}

                
				//5.8 ---> STEP: MEMO
                $memos = DB_DataObject::factory('memos');
                $memos->id_users = $this->id;
                if ($memos->find()){
                    while($memos->fetch()){
						$all['memos'][] = $this->arc_decrypt_output(array($memos));
                    }

                }	
				
		
				
		$serialized = serialize($all);
		unset($all);
		$file = array();
		$file['file'] = base64_encode($this->arc_encrypt($serialized, $this->request['export_password1']));
		unset($serialized);
		$file['size'] = mb_strlen($file['file'], '8bit');
		$file['type'] = 'application/encrypted';
		$file['name'] = 'arcanum_encrypted_export.arc';

		ob_end_clean();
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/' . $file['type']);
		header('Content-Disposition: attachment; filename="' . $file['name'].'"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: private');
		header('Content-Length: ' . $file['size']);
	
		$this->user_log(e("exportall"));
	
		echo ($file['file']);
		die();
	}
	
	
	public function show (){
	
	}
	
	public function printview (){
		
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
