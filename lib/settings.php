<?php 
class settings extends arcanum {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function show () {

		$settings = DB_DataObject::factory('settings');
		$settings->id_users = $this->id;
		$settings->find(TRUE);
		
		$this->data[] = $settings;		
		$this->content['colour'] = $this->colour;
		$this->content['id'] = $this->id;
		$this->content['hashid'] = $this->arc_hash($this->id);
		
		$user = DB_DataObject::factory('users');
		$user->selectAdd();
		$user->selectAdd('lastupdated');
		$user->get($this->id);
		//var_dump($user->lastupdated);
		
		if ($user->lastupdated != ""){
			$this->content['lastupdated'] = $this->session_get('lastupdated');
		}
		
		if (isset($this->content['lastupdated'])){
			if (($this->content['lastupdated'] + $this->recommend_password_change_interval) < TIME){
				$this->content['lastupdated_to_old'] = TRUE;
				debug ('$this->content["lastupdated"]',$this->content['lastupdated']);
				debug ('$this->recommend_password_change_interval', $this->recommend_password_change_interval);
				debug ('time', TIME);
			} else {
				$this->content['lastupdated_to_old'] = FALSE;
			}
		}


		if ($this->forgot_active === TRUE){
	            $forgot = DB_DataObject::factory('forgot');
        	    $forgot->id_users = $this->id;

	            if (($forgot->find(TRUE)) && ($this->arc_decrypt_output($settings->use_forgot) == "yes")){
					$this->content['forgot_set'] = TRUE;

					if (isset($this->request['username_forgot_change']) && isset($this->request['password_forgot_change'])){
						if ($this->arc_check_login($this->request['username_forgot_change'], $this->request['password_forgot_change'])){
					                $username = $this->request['username_forgot_change'];
			
	                				if ($forgot->username == $this->arc_encrypt($username, $username)) {
						                $this->content['forgot_question'] = $this->arc_decrypt($forgot->question, $username);
						                $this->content['forgot_answer'] = $this->arc_decrypt($forgot->answer, $username);
						                $this->content['forgot_hint'] = $this->arc_decrypt($forgot->hint, $this->content['forgot_answer']); 
					
								$this->content['forgot_show_now'] = TRUE;
							} else {
								throw new arcException('No forgot data found for user ID ['.$this->id.']?');
							}
						} else {
							$this->session_msg(e('credentials_false'));				
						}
					}
			} elseif ( !($forgot->find(TRUE)) && ($this->arc_decrypt_output($settings->use_forgot) == "yes")){
				$this->content['forgot_show_now'] = TRUE;
			}

		}

		$this->content['langs'] = $this->arc_langs;

		$this->content['patternlock_active'] = ( ($settings->patternlock != '') || ($this->session_exists('patternlock_active')) ) ? TRUE : FALSE;
		
	}

	public function change_settings () {
		
		if (isset($this->request['patternlock'])){
			if (($this->request['patternlock'] == '') || (strlen($this->request['patternlock']) < $this->min_patterlock_lenght)){
				$this->session_msg( e('patternlock_empty',array($this->min_patterlock_lenght)) );
				
				redirect('settings');
			
			} else {
				$new_vals['patternlock'] = $this->request['patternlock'];
			}	
					
		} else {
	
			if (!($this->crc == $this->request['crc_options_change']))
				redirect('settings');
				
			$new_vals['use_autolinkgen'] = (@$this->request['use_autolinkgen'] == 'on') ? 'yes' : 'no';
			$new_vals['hide_desc'] = (@$this->request['hide_desc'] == 'on') ? 'yes' : 'no';
			$new_vals['hide_comment'] = (@$this->request['hide_comment'] == 'on') ? 'yes' : 'no';
			$new_vals['use_forgot'] = (@$this->request['use_forgot'] == 'on') ? 'yes' : 'no';
	
			if (@$this->request['patternlock_active'] == 'on') {
				$this->session_set('patternlock_active', TRUE);
			} else {
				$this->session_del('patternlock_active');
				$delpattern = TRUE;
			}
			
			############## FORGOT
			if ($this->forgot_active === TRUE){
				
				$forgot = DB_DataObject::factory('forgot');
				$forgot->id_users = $this->id;
	
				if ($forgot->find(TRUE)){
					if ($new_vals['use_forgot'] == 'no') {
						$forgot->delete();
					}
				}
				
			}
			#####
			
			if (in_array($this->request['lang'], $this->arc_langs))
				$new_vals['lang'] = $this->request['lang'];
		
			if (in_array($this->request['start_module'], $this->registered_start_modules))
				$new_vals['start_module'] = $this->request['start_module'];
			
			if (is_int((int)$this->request['session_lifetime'])){
				$time_req = $this->request['session_lifetime']*60;			
				if (!($time_req > $this->max_session_lifetime)){
					
					if ($time_req >= $this->min_session_lifetime){			
						$this->session_set('lifetime', $time_req);
						$new_vals['session_lifetime'] = $time_req;
					}
				}	
			}

	
                        if (is_int((int)$this->request['arc_pass_notify_interval'])){
				$arc_pass_notify_interval = $this->request['arc_pass_notify_interval'];
                                if (!($arc_pass_notify_interval > $this->max_arc_pass_notify_interval)){
                                        if ($arc_pass_notify_interval >= $this->min_arc_pass_notify_interval){
                                                $new_vals['arc_pass_notify_interval'] = $arc_pass_notify_interval*24*60*60;
						if ($new_vals['arc_pass_notify_interval'] == 0)
							$new_vals['arc_pass_notify_interval'] = -1;
                                        }
                                }
                        }



		}
		
		$settings = DB_DataObject::factory('settings');
		$settings->id_users = $this->id;
		$fund = $settings->find(TRUE);
		
		$settings = $this->arc_encrypt_input($settings, $new_vals);
		
		if (isset($delpattern))
			$settings->patternlock = '';
				
		$ret = ($fund) ? $settings->update() : $settings->insert();		
	
		$this->session_msg(e('personal_settings_saved'));
		redirect('settings');
	}


	public function forgot_change () {

	if ($this->forgot_active != TRUE){
		redirect('settings');
		exit;
	}

	if ($this->arc_check_login($this->request['username_forgot_change'], $this->request['password_forgot_change'])){

		if ((isset($this->request['question'])) && (isset($this->request['answer'])) && (isset($this->request['hint']))) {
			
			if ((!preg_match($this->forgot_preg, $this->request['question'])) || (!preg_match($this->forgot_preg, $this->request['answer']))  || (!preg_match($this->forgot_preg, $this->request['hint']))) {

				$this->session_msg(e('forgot_preg_mismatch').'! '.e('forgot_preg_allowed').': '.$this->forgot_preg);
				
			} else {

				$forgot = DB_DataObject::factory('forgot');
				$forgot->id_users = $this->id;
	
				$fund = $forgot->find(TRUE);
				$username = $this->request['username_forgot_change'];
	
				$forgot->username = $this->arc_encrypt($username, $username);
			        $forgot->question = $this->arc_encrypt($this->request['question'], $username);
			        $forgot->answer = $this->arc_encrypt($this->request['answer'], $username);
	        		$forgot->hint = $this->arc_encrypt($this->request['hint'], $this->request['answer']);
				$forgot->active = 'yes';
	
				if ($fund){
				        $forgot->update();
				} else {
					$forgot->insert();
				}
				$this->session_msg(e('saved'));
			}
		
		} 
		redirect("settings");
        exit;
		
	} else {
	    $this->session_msg(e('credentials_false'));
        redirect("settings");
        exit;
	}

	}
	
	
	public function change_password () {
	
		if ($this->crc == $this->request['crc_passwd_change']){
			if ((trim($this->request['password_1']) == trim($this->request['password_2'])) && (trim($this->request['username'] != "")) && ($this->request['password_old'] != "")){
				
				/////////// PREVENT SAME PASSWORD/USERNAMES FOR DIFFERENT USERS
				$login = $this->arc_encrypt($this->arc_hash($this->request['username']), $this->arc_hash($this->request['password_1']));
                                $password = $this->arc_encrypt($this->arc_hash($this->request['password_1']), $this->arc_hash($this->request['username']));

                                $new = DB_DataObject::factory('users');
                                $new->login = $login;
                                $new->password = $password;
				/////////////////////////////////////		

                                if($new->find() === 0)
					$this->arcanum_change_holy('password', $this->request['password_1']);
				else
					$this->session_msg(e('password_not_possible'));
			
			} else {
				$this->session_msg(e('credentials_false'));
			}
		}
		redirect('settings');
	}

	
	public function change_colour () {
		if ($this->crc == $this->request['crc_colour_change']){
			if ($this->arc_check_login($this->request['username'], $this->request['password_old'])){

				if (trim($this->request['new_colour']) != ""){
					$this->arcanum_change_holy('colour', $this->request['new_colour']);
				} else {
					$this->session_msg(e('credentials_false'));
					redirect("settings");
					exit;
				}
				
			} else {
				$this->session_msg(e('credentials_false'));
				redirect("settings");
				exit;
			}
		}
	}


	public function delete_me (){
		
		if ($this->crc == $this->request['crc_delete_user']){
			
			if ($this->arc_check_login($this->request['username'], $this->request['password_old'])){
		
				if ($this->inv_mode === TRUE) {				
					$inv = DB_DataObject::factory('invitations');
					$inv->id_active = $this->id;
					$inv->find(TRUE);				
					$inv_id = $inv->id;				
					$inv->id_active = '-1';
					$inv->update();
				}
				
				
				$users = DB_DataObject::factory('users');
				$users->id = $this->id;
				$users->find(TRUE);
		
				$users->delete();
								
				$xlog = ($this->inv_mode === TRUE) ? 'Inv ID was '.$inv_id : '';		
				logit('Account ' . $this->id . ' removed '.$xlog);
				
				$this->kill();
				redirect('login', 'logout', e('account_deleted'));
			
			} else {
				$this->session_msg(e('credentials_false'));
				redirect("settings");
				exit;
			}

		} 
		
	}
	
	/*
	public function change_username (){
	
	}
	*/

}
?>
