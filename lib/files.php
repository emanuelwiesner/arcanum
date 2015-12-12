<?php 
class files extends arcanum {
	
	
	public function __construct(){
		parent::__construct();
		
	}

	public function countinvs () {
		$invitations = DB_DataObject::factory('invitations');
                $invitations->id_users = $this->id;
		$invitations->whereAdd('id_active > 0');
		return ($invitations->count());
	}
	
	public function download () {
		$this->setlayout = FALSE;
			
		$this->check_permissons('files', $this->request['id_file']);
		
		$files = DB_DataObject::factory('files');
		$files->id = $this->request['id_file'];	
		$files->selectAdd();
		$files->selectAdd('file,type,size,name');		
		$files->find(TRUE);	
		
		$out = $this->arc_decrypt_output( array($files) );		

		$file = $out[0];
		$this->user_log("[".$file['name']."] ".e('file_downloaded'));

		ob_end_clean();
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/' . $file['type']);
		header('Content-Disposition: attachment; filename="' . $file['name'].'"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: private');
		header('Content-Length: ' . $file['size']);
		
		echo ($file['file']);
		die();		
	}
	
	public function show ($output = TRUE){

		$categories = DB_DataObject::factory('categories');
		$categories->id_users = $this->id;
		
		$this->content['hashid'] = $this->arc_hash($this->id);
		
		$cats = array();
		if ($categories->find()){

			while($categories->fetch()){
				$cats[$categories->id] = $categories->category;
				$this->data['c_' . $categories->id] = clone($categories);
			}
	
		}

		if (count($cats)){
			foreach ($cats as $id => $name){
				$files = DB_DataObject::factory('files');
				$files->id_categories = $id;					
				$files->selectAdd();
				$files->selectAdd('id,id_categories,name,size,type,comment,date');
					
				$files->category_name = $name;
		
				$num = $files->find();
				if ($num){
					while($files->fetch()){
						$this->data[$files->id] = clone($files);
					}				
				}
			}
		}
		if ($output == FALSE){
			$this->useview = $output; 
		}

		if ($this->inv_mode === TRUE)	
			$this->content['invs'] = $this->countinvs();
	}
	
	
	public function add (){
		ini_set('upload_max_filesize', $this->max_file_size);	
		if (!($this->get_arc_lock())){
			$this->session_msg(e('still_in_progress'));
			redirect("files");
		}
		$this->put_arc_lock(e('file_checking'));
		sleep(1);
		
		$this->check_permissons('categories', $this->request['cat']);
		
		$files = DB_DataObject::factory('files');		
		$files->id_categories = $this->request['cat'];
		
		$newvalues = $_FILES['thefile']; 
		
		$this->show(FALSE);
		$usedsize = 0;
		

	        if ($this->inv_mode === TRUE) {
        	        $this->max_data_storage += $this->storage_per_inv * $this->countinvs();
                	$this->max_data_storage = ($this->max_data_storage > $this->storage_upper_limit) ? $this->storage_upper_limit : $this->max_data_storage;

        	}

		foreach ($this->data as $key => $file){
			if (preg_match("%^c_%", $key))
				continue;
			
			$usedsize = $usedsize + $this->arc_decrypt_output($file->size);		
		}
		
		if ($newvalues['error'] != "0") {
			$this->session_msg(e('upload_problem'));
		
		} elseif (($newvalues['size'] + $usedsize) > $this->max_data_storage) { 
		
			$this->session_msg("[".$newvalues['name']."] " .e('no_space_left'));
		
		} elseif ($newvalues['size'] <= $this->max_file_size){

			$this->session_msg("[".$newvalues['name']."] ".e('file_success'));
			if ($this->enablevirusscan === TRUE){
			
				$this->put_arc_lock(e('file_virusscan'));
				$vout = system($this->virusscanner .' '. escapeshellcmd($_FILES['thefile']['tmp_name']), $ret);

		        if ($ret == 0) {
					$newvalues['file'] = file_get_contents($_FILES['thefile']['tmp_name']);			
					
					if (isset($this->request['comment']))
						$newvalues['comment'] = $this->request['comment'];

					$newvalues['date'] = TIME; 
				
					$this->put_arc_lock(e('file_encrypting'));
					$save_file = $this->arc_encrypt_input($files, $newvalues);
					
					$this->put_arc_lock(e('file_saving'));
					$ret = $save_file->insert();
					sleep(1);
				} else {
					$this->session_msg("[".$newvalues['name']."] ".e('file_virus').$vout);
				}
			}
		
		} else {
			$this->session_msg("[".$newvalues['name']."] ".e('file_too_big'));
		}
		
		$this->del_arc_lock();
		unlink($_FILES['thefile']['tmp_name']);
		redirect('files');
	}
	
	
	
	public function del () {		
		$this->check_permissons('files', $this->request['id_file']);
		
		$files = DB_DataObject::factory('files');
		$files->id = $this->request['id_file'];
			
		$files->find(TRUE);
		$filename = $this->arc_decrypt_output($files->name);
		$files->delete();
		
		$this->session_msg("[".$filename."] ".e('file_deleted'));
	
		redirect('files');	
	}
	
}
?>
