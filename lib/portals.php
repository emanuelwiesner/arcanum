<?php 
class portals extends arcanum {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function show (){
		$id = $this->request['id'];
		$this->check_permissons('categories', $id);
		
		if (isset($this->request['portstoshow'])){
			$p = json_decode($this->request['portstoshow']);
			foreach ($p as $pid){
				if ($pid != ''){
					if (is_numeric($pid)){
						$this->check_permissons('portals', $pid);
						$portstoshow[] = $pid;	
					}
				}
			}
		}
		$settings = DB_DataObject::factory('settings');
		$settings->id_users = $this->id;
		$settings->find(TRUE);
		
		$use_autolinkgen = FALSE;
		if ( $settings->use_autolinkgen == $this->arc_encrypt_input('yes') )
			$use_autolinkgen = $content['use_autolinkgen'] = TRUE;

		$portals = DB_DataObject::factory('portals');
		$portals->id_categories = $id;
		
		if ($this->request['opt'] == 'favs'){
			$portals->common_used = $this->arc_encrypt_input("yes");
		}

		if (isset($portstoshow)){
			foreach ($portstoshow as $pid)
				$portals->whereAdd('id = '.$pid, 'OR');
		}
		
		if ($portals->find()){
		
			$logthis = array();
			while($portals->fetch()){
				$this->data[$portals->id] = clone($portals);
				
				if ($use_autolinkgen){
					if ($portals->link != ""){
						$arcanum = DB_DataObject::factory('arcanums');
						$arcanum->id_portals = $portals->id;
						$arcanum->active = $this->arc_encrypt_input("y");
						if ($arcanum->find()){					
							while($arcanum->fetch()){
								$arcanum_out = $this->arc_decrypt_output(array(clone($arcanum)));
								$this->content[$portals->id] = $arcanum_out[0];
								if ($arcanum->portal_pass != '')
									$logthis[] = $this->arc_decrypt_output($portals->name);
							}						
						}
					}
				}				
			}

			if (count($logthis) > 1) {
				$logtext = '';
				$o = 1;
				foreach ($logthis as $log) {
	                                $logtext = ($o == count($logthis)) ? $logtext.$log : $logtext.$log.', ';
					$o++;
				}
				$logtype = 'portals_opened_auto';
			} else {
				$logtext = $logthis[0];
				$logtype = 'portal_opened_auto';
			}
		
			$this->user_log(e($logtype).": ".$logtext);			
		}

		if (!(isset($this->data))){
			$this->content = '';
			$this->useview = FALSE;
		}
		$this->setlayout = FALSE;
	}
	
	public function update (){		
		$id = $this->request['id_port'];
		
		$this->check_permissons('portals', $id);
	
		$portals = DB_DataObject::factory('portals');
		$portals->get($id);
		
		if ($this->request['autolink'] == '1'){
			$this->request['link'] = $this->autologinprepend . $this->request['link'];
		}
		
		$portal_updated = $this->arc_encrypt_input(clone($portals), $this->request);
		
		$portal_updated->update();
		
		$port_name = $this->arc_decrypt_output($portals->name);
		$this->user_log("[".$port_name."] ".e('portal_changed'));
		
		$this->content = 1;
		$this->useview = FALSE;
		//redirect('categories');				
	}
	
	public function add (){
		$this->check_permissons('categories', $this->request['id_categories']);
		
		$new_portal = DB_DataObject::factory('portals');
	
		$this->request['common_used'] = ($this->request['common_used'] == 'on') ? 'yes' : 'no';
		$this->request['active'] = '1';
		
		if ($this->request['autolink'] == '1'){
			$this->request['link'] = $this->autologinprepend . $this->request['link'];
		}
		
		$new_portal = $this->arc_encrypt_input($new_portal, $this->request);
		
		$portalid = $new_portal->insert();

		################
		
		if (is_numeric($portalid)){			
			$new_arcanum = DB_DataObject::factory('arcanums');
			$new_arcanum->id_portals = $portalid;
			$this->request['created'] = TIME;
			$this->request['active'] = "y";
			$new_arcanum = $this->arc_encrypt_input($new_arcanum, $this->request);
			$id_arcanum = $new_arcanum->insert();
		}
		
		################
		
		$this->session_msg("[".$this->request['name']."] ".e('portal_created'));
		
		$this->content = '1';
		$this->useview = FALSE;
		//redirect('categories');			
	}
	
	
	public function del () {
		$portals = DB_DataObject::factory('portals');
		$portals->get($this->request['id_port']);
		
		$portals->find(TRUE);
		$port_name = $this->arc_decrypt_output($portals->name);
		$portals->delete();		
		
		$this->content = 1;		
		$this->useview = FALSE;		
		
		$this->user_log("[".$port_name."] ".e('portal_deleted'));		
	}
	
	
	public function common_used_change () {
		
		$id = $this->request['id_port_change_common_used'];
		$old = $this->request['port_change_common_used_old_val'];
		
		$this->check_permissons('portals', $id);
		
		$portal_new_used_val['common_used'] = ($old == "no") ? "yes" : "no";  
	
		$portals = DB_DataObject::factory('portals');
		$portals->get($id);
		
		$portal_upd = $this->arc_encrypt_input(clone($portals), $portal_new_used_val);
		$portal_upd->update();
		
		die('changed to ' . $portal_new_used_val['common_used']);
	}
}
