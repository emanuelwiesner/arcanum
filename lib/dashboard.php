<?php 
class dashboard extends arcanum {
	
	public function __construct(){
		parent::__construct();
	}

	public function showall () {
		$this->show(TRUE);
	}

	public function exportall () {
		$w = "secure medj2380fge2gd23fhewifhgewfh0ewfhg08g4308fg0320gd320";
		$password = "dj2380fge2gd23fhewifhgewfh0ewfhg08g4308fg0320gd320";
		$test = $this->arc_encrypt ($w, $password);
		die("=> [".$test."] <=");
	}
	
	public function show ($showall = FALSE) {
		//LAST PW UPDATE
		$user = DB_DataObject::factory('users');
		$user->selectAdd();
		$user->selectAdd('lastupdated');		
		$user->get($this->id);
		
		$db = &$user->getDatabaseConnection();
		
		if (defined('DEBUG_ON'))
			var_dump($user->lastupdated);
		
		if ($user->lastupdated != ""){		
			$this->content['lastupdated'] = $this->session_get('lastupdated');
		}		
	

		if ($this->session_get('logged_in_and_default_site_shown') == TRUE){
			$this->session_del('logged_in_and_default_site_shown', FALSE);
			define('JUST_LOGGED_IN', TRUE);
		}
	
		if (isset($this->content['lastupdated'])){
			if (($this->content['lastupdated'] + $this->recommend_password_change_interval) < TIME){
				$this->content['lastupdated_to_old'] = TRUE;
				debug ('$this->content["lastupdated"]',$this->content['lastupdated']);
				debug ('$this->recommend_password_change_interval', $this->recommend_password_change_interval);				
				debug ('TIME', TIME);
			} else {
				$this->content['lastupdated_to_old'] = FALSE;
			}
		}
		
		
		$this->content['lastlogin'] = $this->session_get('lastlogin'); //LAST LOGIN	

		$settings = DB_DataObject::factory('settings');
		$settings->id_users = $this->id;
		$settings->find(TRUE);

		if ($this->forgot_active === TRUE){ //LAST PW FORGOT REQUEST			
			if ($this->session_exists('forgot_displayed_since_last_login'))
				$this->content['forgot_hint_req_since_last_login'] = $this->session_get('forgot_displayed_since_last_login');
				
			if ($settings->use_forgot == '')
				$this->content['use_forgot_not_active'] = TRUE;				
		}

		$arc_pass_notify_interval = $this->arc_decrypt_output($settings->arc_pass_notify_interval);

		$showoutdated = ($arc_pass_notify_interval < 0) ? FALSE : TRUE;
		$arc_pass_notify_interval = (is_int((int)$arc_pass_notify_interval) && ($arc_pass_notify_interval > 0))  ? $arc_pass_notify_interval : $this->recommend_password_change_interval;
		$this->content['arc_pass_notify_interval'] = $arc_pass_notify_interval;
			
	
		//Data Stats.
		$this->content['count_port']  = $this->content['count_cat'] = $this->content['count_files'] = 0;
		
		$categories = DB_DataObject::factory('categories');
		$categories->id_users = $this->id;
		$this->content['count_cat'] = $categories->find();
		
		if ($this->content['count_cat']) {
			$enc_pws_arr = array();
			while ($categories->fetch()) {				
				$portals = DB_DataObject::factory('portals');
				$portals->id_categories = $categories->id;
				$this_find = $portals->find();
				$this->content['count_port'] = $this_find + $this->content['count_port'];
				
				if ($this_find){
					while ($portals->fetch()){
						$arcanums = DB_DataObject::factory('arcanums');
						$arcanums->id_portals = $portals->id;
						$arcanums->active = $this->arc_encrypt_input('y');
						
						if ($arcanums->find()){
							while ($arcanums->fetch()){
								$enc_pw = $this->arc_decrypt_output($arcanums->portal_pass);
								$enc_time = $this->arc_decrypt_output($arcanums->created);
								$rem_time = $this->arc_decrypt_output($arcanums->remember);
								$remtime = (is_numeric($rem_time)) ? $rem_time : 0;
								if ( ($this->validate_password($enc_pw) < 3) && ($enc_pw != '') && (($showall == TRUE)|| ($rem_time + $arc_pass_notify_interval) < TIME))
									$this->content['bad_pws'][$this->arc_decrypt_output($categories->category)][$this->arc_decrypt_output($portals->name)] = $arcanums->id; 
								if ( ($showoutdated)  && ($enc_pw != '') ){
									if ((($enc_time + $arc_pass_notify_interval) < TIME) && (($showall == TRUE) || ($rem_time + $arc_pass_notify_interval) < TIME))
										$this->content['old_pws'][$this->arc_decrypt_output($categories->category)][$this->arc_decrypt_output($portals->name)] = $arcanums->id;
								}								

								if (in_array($enc_pw, $enc_pws_arr) && ($enc_pw != '' ))
									$this->content['double_pws'][$this->arc_decrypt_output($categories->category)][$this->arc_decrypt_output($portals->name)] = $arcanums->id;
								
								$enc_pws_arr[] = $enc_pw;
							}
						}
					}
				}
				
				$files = DB_DataObject::factory('files');
				$files->id_categories = $categories->id;
				$this->content['count_files'] = $files->find() + $this->content['count_files'];
				
			}			
			unset($enc_pws_arr);
		}

		$this->content['showall'] = $showall;

		if ($this->inv_mode === TRUE) {
                        $inv = DB_DataObject::factory('invitations');
                        $inv->id_users = $this->id;
                        $all = $inv->find();
                        $inv->id_active = 0;
                        $this->content['count_inv_open'] = $inv->find();
			$this->content['count_inv_closed'] = $all - $this->content['count_inv_open'];
		}


	}
	
}
?>
