<?php 
class arcanums extends arcanum {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function show (){
		$id = $this->request['id'];
		
		$this->check_permissons('portals', $id);

		$portal = DB_DataObject::factory('portals');
		$portal->get($id);

		$this->user_log(e('portal_opened').": ".$this->arc_decrypt_output($portal->name));
	
		$arcanum = DB_DataObject::factory('arcanums');
		$arcanum->id_portals = $id;
		if ($arcanum->find()){
	
			while($arcanum->fetch()){
				$this->data[$arcanum->id] = clone($arcanum);
			}
	
		}
		$this->content['portal_id'] = $id;
		$this->setlayout = FALSE;
	}

	public function remember () {
		$id = $this->request['id_arc'];
		$this->check_permissons('arcanums', $id);

                $arc = DB_DataObject::factory('arcanums');
                $arc->get($id);
	
		$arc_upd = $this->arc_encrypt_input(clone($arc), array('remember' => TIME));
		$arc_upd->update();

                $this->content = 1;
                $this->useview = FALSE;
	}
	
	public function update (){
		$id = $this->request['id_arc'];		
		$this->check_permissons('arcanums', $id);
		
		$old_arc = DB_DataObject::factory('arcanums');
		$old_arc->get($id);		
		
		$portalid = $old_arc->id_portals; //!!!
		
		//olds to active = n
		$old_arcs = DB_DataObject::factory('arcanums');
		$old_arcs->id_portals = $portalid;		
		if ($old_arcs->find()){		
			while($old_arcs->fetch()){
				$n_enc = $this->arc_encrypt_input('n');
				
				if ($old_arcs->active != $n_enc){
					$old_arcs->active = $n_enc;				
					$old_arcs->update();
				}
			}		
		}

		$new_arcanum = DB_DataObject::factory('arcanums');
		$new_arcanum->id_portals = $portalid;
		$this->request['active'] = "y";
		$this->request['created'] = TIME;
		$new_arcanum = $this->arc_encrypt_input($new_arcanum, $this->request);
				
		$new_arcanum->insert();
		$this->content = 1;		
		
		$port = DB_DataObject::factory('portals');
		$port->get($portalid);
		$portal = $this->arc_decrypt_output($port->name);
		$this->user_log(e('arcanums_changes') . ': [' . $portal . ']');
		
		$this->useview = FALSE;
	}
}
