<?php
class search extends arcanum {

	protected $ret;

        public function __construct(){
                parent::__construct();
	
		$this->ret = array();
		$this->ret['ret'] = array();
		$this->ret['ret']['portals'] = array();
		$this->ret['ret']['categories'] = array();

		$this->ret['count'] = 0;
	}

	public function show () {
		$this->live();
	}


	public function add_result ($category_id, $portal_id) {

		if (!(isset($this->ret['ret']['portals'][$category_id])))
                	$this->ret['ret']['portals'][$category_id] = array();
		
		$this->ret['ret']['portals'][$category_id][] =  $portal_id;


                if (!(in_array($category_id, $this->ret['ret']['categories'])))
                	$this->ret['ret']['categories'][] = $category_id;


		$this->ret['count']++;
	}


	public function live () {
		
		if (strlen($this->request['q']) < $this->min_search_chars)
			die(e('min_fn_l').$this->min_search_chars);
		
		$searchlist = array('desc' , 'name' , 'link');
		$q = str_replace('*', '.*',strtolower($this->request['q']));
		
		$categories = DB_DataObject::factory('categories');
		$categories->id_users = $this->id;
		
		$count = 0;
		$ret = $ret_categories = array();
	
		if ($categories->find()){
			 
			while($categories->fetch()){
				$portals = DB_DataObject::factory('portals');
				$portals->id_categories = $categories->id;
				if ($portals->find()){
					 
					while($portals->fetch()){
						$treffer = FALSE;
						$portarray = $this->arc_decrypt_output(array(clone($portals)));
						foreach ($searchlist as $s){
							if ($treffer == FALSE){
								if (preg_match('%.*'.$q.'.*%', strtolower($portarray[0][$s]))){

									$this->add_result($categories->id, $portals->id);
									$treffer = TRUE;

								} else {
									$arcanums = DB_DataObject::factory('arcanums');
					                                $arcanums->id_portals = $portals->id;
					                                $arcanums->active = $this->arc_encrypt_input("y");
					                                if ($arcanums->find()){
				                                        	while($arcanums->fetch()){
				                                                	$arcanums_array = $this->arc_decrypt_output(array(clone($arcanums)));
					                                        	if (preg_match('%.*'.$q.'.*%', strtolower($arcanums_array[0]['portal_login'])) || preg_match('%.*'.$q.'.*%', strtolower($arcanums_array[0]['portal_pass'])) ){
                                                                        			$this->add_result($categories->id, $portals->id);
												$treffer = TRUE;
											}
										}

				                                	}

								}
							}
						}
					}
					 
				}
				 
			}
			 
		}
		
		$this->content = json_encode($this->ret);
		$this->useview = $this->setlayout = FALSE;
	}


	public function memo () {		
		if (strlen($this->request['q']) < $this->min_search_chars)
			die(e('min_fn_l').$this->min_search_chars);
		
		$searchlist = array('title' , 'note');
		$q = str_replace('*', '.*',strtolower($this->request['q']));
		
		$memos = DB_DataObject::factory('memos');
		$memos->id_users = $this->id;
		
		$this->ret = $this->ret['ret'] = array();
		$this->ret['count'] = 0;
		if ($memos->find()){			 
			while($memos->fetch()){
				$memo_array = $this->arc_decrypt_output(array(clone($memos)));
				$treffer = FALSE;				
				foreach ($searchlist as $s){
					if ($treffer == FALSE){
						if (preg_match('%.*'.$q.'.*%', strtolower($memo_array[0][$s]))){
							$treffer = TRUE;
							if (!(in_array($memos->id, $this->ret['ret']))){
								$this->ret['ret'][] = $memos->id;
								$this->ret['count']++;
							}
						}
					}
				}
			}
		}
		
		$this->content = json_encode($this->ret);
		$this->useview = $this->setlayout = FALSE;
	}
}
?>
