<?php 
class invitation extends arcanum {
	
	public function __construct(){
		parent::__construct();
	}
	
	
	public function add (){
		
		if (filter_var($this->request['receipient'], FILTER_VALIDATE_EMAIL)){	
		
			if ($this->get_left_inv() > 0){
				
				$uniq = $this->gen_unique($this->request['receipient']);
				
				$invationcheck = DB_DataObject::factory('invitations');
				$invationcheck->id_invhash = $uniq;
				
				if ($invationcheck->find(TRUE)) {
					$invationcheck->fetch();
					$this->session_msg($this->request['receipient'].' '.e('already_invated'));
				} else {
							
						
						$message = e('auto_inv_pre_text');
						$message .= "\n-----------------\n";
						$message .= trim(ltrim(trim($this->request['message']),'.'));						
						$message .= "\n\n-----------------\n";
						$message .= e('auto_follow_this')."\n".link_for('register', 'invite&code='.$uniq);						
						$message .= "\n\n".e('auto_days_valid'). ' [' .$this->inv_valid_time_days ."]\n";
						$message .= e('auto_inv_post_text', array($this->inv_abuse_mailaddr));
						
						if (mail($this->request['receipient'], e('auto_inv_subj'), $message, $this->inv_mailh)){
						
							$invitations = DB_DataObject::factory('invitations');
							$invitations->id_users = $this->id;
							
							$this->request['time'] = TIME;
							$this->request['id_invhash'] = $this->gen_unique($this->request['receipient']);
							$this->request['id_active'] = 0;
							
							$save_invation = $this->arc_encrypt_input($invitations, $this->request);
							$save_invation->insert();
			
							logit("[".$this->id."] generated hash [".$this->request['id_invhash']."] and invitated someone.");
							$this->session_msg($this->request['receipient'] .' '. e("successfully_invated"));
						} else {
							throw new arcException("[".$this->id."] Problem sending Invationmail TO SOMEONE.");
						}

				}
				
			} else {
				$this->session_msg(e('inv_max_invs').' '.$this->max_invs_per_month);
			}
			
		} else {
			$this->session_msg($this->request['receipient'].' '.e('invalid_mail_address'));
		}
		
		redirect('invitation');
	}
	
	public function show (){

		$this->content['left'] = $this->get_left_inv();		
		
		$invitations = DB_DataObject::factory('invitations');
		$invitations->id_users = $this->id;
		//$invitations->orderBy('time DESC');
		
		if ($invitations->find()){
		
			while($invitations->fetch()){
				$this->data[$invitations->id] = clone($invitations);
				
				if ($invitations->id_active == 0){
					$this->content['open'][] = $invitations->id;
				} elseif ($invitations->id_active == '-1') {
					$this->content['deleted'][] = $invitations->id;
				} else {
					$this->content['closed'][] = $invitations->id;
				}
			}
				
		}
		
	}	
	

	public function get_left_inv () {
		$invitations = DB_DataObject::factory('invitations');
		$invitations->id_users = $this->id;
		
		$inv_left = $this->max_invs_per_month;
		if ($invitations->find()){
				
			while($invitations->fetch()){
				if (strftime($this->invs_time_equal_param) == strftime($this->invs_time_equal_param, $this->arc_decrypt_output($invitations->time)))
					$inv_left--;
			}
		
		}
		return 	$inv_left;
	}
	
	
	public function gen_unique ($mailaddress = FALSE) {
		return ($mailaddress == FALSE) ? mailaddress_hash($this->request['mailaddress']) : mailaddress_hash($mailaddress);		
	}
	
}
?>
