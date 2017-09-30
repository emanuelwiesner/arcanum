<?
/**
  * BEGIN CONTROLLER CLASS arcanum ~ 26.Oktober 2011 EW 
  * 
  * * Main ARC Controller
  * 
  * @author  Emanuel <emanuel.wiesner@writtendreams.de>
  */

class arcanum {

    // ---------
	
	protected $session;
	protected $conf;

	public $modulename;
	
	protected $request;
	
	protected $content; //messages from model to user
	protected $data; //encrypted data from model to user
	
	private $homepage; //users start page
	
	private $session_cryptkey; //var which contains session crypt keys
	
	protected $colour; //users fav colour
	protected $id; //users id
	private $masterkey; //users masterkey (decrypted, from session)
	
	
	
	######## ::VARS ###########	
	######## FUNCTIONS:: ###########
		
	
	/*	
	 *  CONSTRUCTOR
	 * 
	 */
	public function __construct ($dbconnect = TRUE){

		ob_start(); 
		global $config;

		##CONFIG
		foreach ($config as $confvar => $confval){
			if ($confval !== '')
				$this->$confvar = $confval;
		}
		$this->modulename = get_class($this);
		
		##VIEW
		$this->setlayout = TRUE;
		$this->useview = TRUE;

		if ($this->maintenance_mode === TRUE) {

			if (UIP  != $this->maintenance_ip){
				header("Status: 503 No Content");
				$this->view_fallback('MAINTENANCE', e('maintenance_msg').'<br />'.e('maintenance_hint'), '');
			} else {
				if ($this->maintenance_debug === TRUE)
					define('DEBUG_ON', TRUE);
			}

		}
		
		##DB
		if ($dbconnect === TRUE){

			//DB_DATAOBJECTS
			############################
			require_once 'DB/DataObject.php'; //pear include				
			//require_once 'MDB2.php';	
			//http://pear.php.net/manual/en/package.database.mdb2.intro-transaction.php	
			//http://pear.php.net/package/DB_DataObject/docs

			if (test_arcanum_db() == FALSE) 
				$this->view_fallback('DB_ERROR', e('db_error_msg'), e('db_error_hint'));
	
			$options = &PEAR::getStaticProperty('DB_DataObject','options');
			$options = array(
			    'database' 		=> $this->database_dsn,
			    'schema_location'   => $this->database_models_location.DS.$this->database_dsn['phptype'],
			    'class_location'    => $this->database_models_location.DS.$this->database_dsn['phptype'],
			    'class_prefix'	=> 'arc_',
			    'driver'		=> 'MDB2',
			    'debug' 		=> $this->database_debug_lv,
			    'ssl'		=> $this->database_ssl_enable,
			    'quote_identifiers' => true
			);
			###########################

		}
	
		##JAILER
		$this->jail('clean_and_check');
	}


	
	/*	
	 *  DESTRUCTOR
	 * 
	 */	
	public function __destruct () {
			
		$this->set_session_hash();
		
	}	

	public function cooldown () {
		//This Function does a incredible nothing amount of things
	}

	
	//GET MAIN DATA
	public function bootstrap($request) {
		
		foreach ($request as $key => $val){
			if (preg_match('%.*id.*%', $key)){
				if (!(preg_match('%.*hide.*%', $key))){
					if (!(is_numeric($val)))
						throw new arcException('User ['.$this->id.'] submitted ['.$val.'] as matching id for KEY ['.$key.'] in module ['.$this->modulename.']');
				}
			}
		}
		
		$this->request = $request;	
	}
	
	
	//CONTROLLERS JOB
	public function execute ($action){
		if (method_exists ($this, $action)){
				$this->action = $action;
				$this->$action();
		} else {
			throw new arcException ("'action [$action] not exists in class $this->modulename !'");
		}
	}

	public function validate_password ($password) {
		
		if ( strlen( $password ) == 0 )
		{
			return 1;
		}
		
		$strength = 0;
		
		/*** get the length of the password ***/
		$length = strlen($password);
		
		/*** check if password is not all lower case ***/
		if(strtolower($password) != $password)
		{
			$strength += 1;
		}
		
		/*** check if password is not all upper case ***/
		if(strtoupper($password) == $password)
		{
			$strength += 1;
		}
		
		/*** check string length is 8 -15 chars ***/
		if($length >= 8 && $length <= 15)
		{
			$strength += 1;
		}
		
		/*** check if lenth is 16 - 35 chars ***/
		if($length >= 16 && $length <=35)
		{
			$strength += 2;
		}
		
		/*** check if length greater than 35 chars ***/
		if($length > 35)
		{
			$strength += 3;
		}
		
		/*** get the numbers in the password ***/
		preg_match_all('/[0-9]/', $password, $numbers);
		$strength += count($numbers[0]);
		
		/*** check for special chars ***/
		preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^\\\]/', $password, $specialchars);
		$strength += sizeof($specialchars[0]);
		
		/*** get the number of unique chars ***/
		$chars = str_split($password);
		$num_unique_chars = sizeof( array_unique($chars) );
		$strength += $num_unique_chars * 2;
		
		/*** strength is a number 1-10; ***/
		$strength = $strength > 99 ? 99 : $strength;
		$strength = floor($strength / 10 + 1);
		
		return $strength;
	}
	
	
	protected function check_permissons ($table, $requested_id){
		
		$ok = FALSE;
		
		if ($requested_id == '')
			throw new arcException('No ID was submitted in check_permissions');
			
		
		if (!(isset($this->perm['categories']))){
			$categories = DB_DataObject::factory('categories');
			$categories->id_users = $this->id;
			$categories->selectAdd();
			$categories->selectAdd('id');
			if ($categories->find()){
				while($categories->fetch()){
					$this->perm['categories'][] = $categories->id;
				}
					
			}
		}
		
		if (!(isset($this->perm[$table])))
			$this->perm[$table] = array();
		
		switch ($table){
			
			case 'categories':				

					if (in_array($requested_id, $this->perm[$table]))
						$ok = TRUE;
				
				break;
			
			case 'files':
				
					if (count($this->perm[$table]) == 0){					
						foreach ($this->perm['categories'] as $id){
							$files = DB_DataObject::factory('files');
							$files->id_categories = $id;
								
							$files->selectAdd();
							$files->selectAdd('id');
								
							if ($files->find()){
								while($files->fetch()){
									$this->perm[$table][] = $files->id;
								}				
							}
						}
					}
	
					if (in_array($requested_id, $this->perm[$table]))
						$ok = TRUE;
			
				break;
				
				
			case 'portals':
					
						if (count($this->perm[$table]) == 0){
							foreach ($this->perm['categories'] as $id){
								$portals = DB_DataObject::factory('portals');
								$portals->id_categories = $id;
						
								$portals->selectAdd();
								$portals->selectAdd('id');
						
								if ($portals->find()){
									while($portals->fetch()){
										$this->perm[$table][] = $portals->id;
									}
								}
							}
						}
					
						if (in_array($requested_id, $this->perm[$table]))
							$ok = TRUE;
						
					break;

			case 'arcanums':
							
						if (!( isset($this->perm['portals']) )){
							foreach ($this->perm['categories'] as $id){
								$portals = DB_DataObject::factory('portals');
								$portals->id_categories = $id;
						
								$portals->selectAdd();
								$portals->selectAdd('id');
						
								if ($portals->find()){
									while($portals->fetch()){
										$this->perm['portals'][] = $portals->id;
									}
								}								
							}
						}
						
						if (count($this->perm[$table]) == 0){
							foreach ($this->perm['portals'] as $id){
								$arcanums = DB_DataObject::factory('arcanums');
								$arcanums->id_portals = $id;
						
								$arcanums->selectAdd();
								$arcanums->selectAdd('id');
						
								if ($arcanums->find()){
									while($arcanums->fetch()){
										$this->perm[$table][] = $arcanums->id;
									}
								}
							}
								
						}
							
						if (in_array($requested_id, $this->perm[$table]))
							$ok = TRUE;
					
					break;

			case 'memos':
					if (count($this->perm['memos']) == 0){
                                        	$memos = DB_DataObject::factory('memos');
                                                $memos->id_userss = $this->id;

                                                $memos->selectAdd();
                                                $memos->selectAdd('id');

                                                if ($memos->find()){
	                                                while($memos->fetch()){
        	                                                $this->perm['memos'][] = $memos->id;
                                                        }
                                                }
                                        }
	
					if (in_array($requested_id, $this->perm[$table]))
						 $ok = TRUE;
					
					break;
		}
		
		
		if ($ok === TRUE){
			return TRUE;
		} else {
			$text = 'User denial [' . $this->id . '] for ID [' . $requested_id . '] on table [' . $table .']';
			throw new arcException($text, 403);
		}
		
	}
	
	/*
	 * Login and authenticate checks
	 * 
	 */
	public function exitnow ($text = '') {
		ob_end_clean();
		die($text);
	}
	
	
	public function authenticate () {

		if ($_GET['module'] == 'lang'){
	        	if (in_array($_GET['code'], $this->arc_langs)){
        	        	setcookie($this->lang_cookiename, $_GET['code'], TIME + (60*60*24*365), substr($this->relpath,0,-1), $_SERVER['HTTP_HOST'], $this->https, TRUE);
			}
			redirect('login');
			
		} 
		
		if ($_GET['module'] == 'status') {
				$file = $this->tmppath.$_GET['sem'];
				if (is_file($file)){
					if (dirname($file).DS == $this->tmppath){
						$this->exitnow(file_get_contents($file));
					} else {
						throw new arcException('someone tried to get which is not in tmppath: ['.realpath($file).']', 666);
					}
				} else {
					$ret = (isset($_GET['upload'])) ? e('uploading') : e('working');
					$this->exitnow($ret);
				}
		}
			
	
		//New User to login
		if ( (isset($_POST['username'])  && isset($_POST['password'])) ){			
			$username = $this->request['username'] = $_POST['username'];
			$password = $this->request['password'] = urldecode($_POST['password']);
			
			if (($username != "") && ($password != "")){
	
				if ($this->arc_check_login($username, $password, TRUE) == TRUE){
					sleep(1);
					
					##TMP Folder clean
					$this->clean_tmp();

					##Check for settings
					$settings = DB_DataObject::factory('settings');
					$settings->id_users = $this->id;
					
					if ($settings->find(TRUE)){

						######################  Startmodule  ############################	
						$start_module = $this->arc_decrypt_output($settings->start_module);
						if (in_array($start_module, $this->registered_start_modules))
								$this->startmodule = $start_module;
						#####################################################################
						
						
						######################  Check PATTERNLOCK  ############################
						$patternlock_user = $this->arc_decrypt_output($settings->patternlock);
						
						//HOWTO: Reset Patternlock
						//if ($username == 'emanuel')
						//	$patternlock_user = '';

						if ($patternlock_user != ''){
							if ($_POST['patternlock'] != '0'){								
								if ($_POST['patternlock'] != $patternlock_user){
										$tries_left = $this->jail('jail');
										$this->kill();					
										$this->exitnow(json_encode(array( 'code' => '1', 'msg' => e('login_fail').e('tries_left', array($tries_left), array(1)) )));
								}
							} else {
								//REQUEST PATTERNLOCK								
								$this->kill();
								$this->exitnow(json_encode(array('code' => '2')));
							}							
						}
						#####################################################################
					}
						
					
					##De-Jail if IP was nominated to block
					$this->jail('dejail');
					
					
					##Cleanup Users log and invs
					$this->user_log_cleanup();					
					if ($this->inv_mode == TRUE)
						$this->user_invitations_cleanup();
					##
					
						
					##Check if Users passwordhint was decrypted since last login
					if ( ($this->forgot_active === TRUE) && ($this->session_exists('lastlogin')) ){
						$forgot = DB_DataObject::factory('forgot');
						$forgot->active = 'yes';
						$forgot->id_users = $this->id;
								
						if ($forgot->find(TRUE)){
							$forgot_last_req = ($forgot->lastreq != '') ? $this->arc_decrypt($forgot->lastreq, $username) : '';
							$forgot_last_req = (is_numeric($forgot_last_req)) ? $forgot_last_req : 0;
					
							if ($forgot_last_req >= $this->session_get('lastlogin')){
								$lastreq_ip = $this->arc_decrypt($forgot->lastreq_ip, $username);			
								$this->user_log(e('question_was_answered'), $this->systemlogger, FALSE, $forgot_last_req, $lastreq_ip);
								$this->session_set('forgot_displayed_since_last_login', $forgot_last_req);

								if ($this->startmodule != 'dashboard'){
									$this->startmodule = 'dashboard';
								}

							}
						}						
					}
					
					###### Do the the login now
					if ($this->startmodule == 'dashboard')
						$this->session_set('logged_in_and_default_site_shown', TRUE);	
					
					$this->log_login();
					$this->exitnow(json_encode(array('code' => '0', 'msg' => link_for($this->startmodule) )));
					#########################################################################################

				} else {
					//die();
					$tries_left = $this->jail('jail');
					$this->exitnow(json_encode(array( 'code' => '1', 'msg' => e('login_fail').e('tries_left', array($tries_left), array(1)) )));
				}
				
			}
		
		//ALREADY LOGGED IN
		} elseif ( ($this->session_get('arc_id') != "") && ($this->session_exists('logintime')) ) {
				
				$sessionlifetime = ini_get('session.gc_maxlifetime');		
				$this->remaining_time = $sessionlifetime - (TIME - $this->session_get('logintime'));

				$arc_key = $this->session_masterkey_get();

				$arc_id = $this->session_get('arc_id');
				
				$login_check = DB_DataObject::factory('users');
				$login_check->find();
				while($login_check->fetch()){
						
					if ($this->arc_hash($login_check->id) == $arc_id) {
						
						//SET COLOUR
						$this->colour = $this->arc_decrypt($login_check->colour, $arc_key);
						
                                                //SET ID
                                                $this->id = $login_check->id;

	                                        //SET MASTERKEY
	                                        $this->masterkey = $arc_key;


						$sec_problem = FALSE;
						##Added securtiy checks
						if (!($this->validate_colour($this->colour))){
							$sec_problem = 'Colour validate';
						}

						##Added securtiy checks
						if ($this->ip_sec_check === TRUE) {
						if (!($login_check->lastip == $this->arc_encrypt(UIP, $arc_key))) {
							logit("IP Changed. Last recognized IP: " . $this->arc_decrypt($login_check->lastip, $arc_key) . "  ---> new IP: " . UIP);	
							$sec_problem = 'IP Missmatch';
							$sec_problem_text = e('logged_out_ip_changed');

							##IPv6 Switch Checker
							if ($this->ipv6switchgraceactive == TRUE) {
								logit("IP Grace Active, Browser: " . UAGENT);
								$update_login_ip = $sec_problem = FALSE;
								
								if (filter_var(UIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
									if ($this->session_exists('lastip6')) {
										if ($this->session_get('lastip6') != UIP) {
											$sec_problem_text .= ' ' . $this->session_get('lastip4') . '->' . UIP;
											$sec_problem = TRUE;
										}
									} else {
										logit("OK, IP4/IP6 Switch, storing new IP");
										$this->session_set('lastip6', UIP);
										$update_login_ip = TRUE;
									}									

								} elseif (filter_var(UIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                                                                        if ($this->session_exists('lastip4')) {
                                                                                if ($this->session_get('lastip4') != UIP) {
											$sec_problem_text .= ' ' . $this->session_get('lastip4') . '->' . UIP;
                                                                                        $sec_problem = TRUE;
                                                                                }
                                                                        } else {
										logit("OK, IP4/IP6 Switch, storing new IP");
                                                                                $this->session_set('lastip4', UIP);
                                                                                $update_login_ip = TRUE;
                                                                        }
								}

								if ($update_login_ip === TRUE) {
									$login_check->lastip = $this->arc_encrypt(UIP, $this->masterkey);
									$login_check->update();
								}
							}
							##IPv6 Switch Checker
						}
						}

                                                ##Added securtiy checks
                                                if (!($login_check->lastbrowser == $this->arc_encrypt(UAGENT, $arc_key))){
							$sec_problem = 'Browser Missmatch';
                                                	$sec_problem_text = e('logged_out_browser_changed');
                                                }

						if ( $sec_problem != FALSE ){
							$this->user_log($sec_problem_text);
							sleep(1);
							$this->kill();
							throw new arcException("Sec_logout [$sec_problem] $sec_problem_text", 408);
							return FALSE;
						}
					
	
						//CHECK SESSION TIMEOUT TTL
						if (($this->modulename != "login") && (@$this->request['action'] != "logout")){
							if ( $this->remaining_time <= 0 ){								
								define('SESSION_TIMEOUT_LOGOUT', TRUE); //if true, user will be JS - logged out in menu.php
							}
						}
						
						
						if (($this->modulename == "login") && (@$_GET['action'] != "logout")){							
							//BUGFIX, after browserrestart
							$this->check_cookies();
							//FIX.
							redirect("dashboard");							
							exit;				
						}
			
					###############
				            $settings = DB_DataObject::factory('settings');
        			            $settings->id_users = $this->id;

			        	    if ($settings->find(TRUE)){
			            		if ($this->arc_decrypt_output($settings->expand_memos) == "yes")
							define('EXPAND_MEMOS', TRUE); //TODO: MEMO_FULL
                        			        
						if ($this->arc_decrypt_output($settings->hide_desc) == 'yes')
			                		define('HIDE_DESC', TRUE);

		                        	if ($this->arc_decrypt_output($settings->hide_comment) == 'yes')
				                	define('HIDE_COMMENT', TRUE);
		
						if (in_array($this->arc_decrypt_output($settings->lang), $this->arc_langs))
							define('LANG', $this->arc_decrypt_output($settings->lang));
                        		   }
			            ###############


		                                if (in_array($this->modulename, $this->registered_modules_no_auth) && ($this->modulename != 'login')) {
							define('MENUAUTH', 'TRUE');
                	                                return FALSE;							
		                                }


						return $this->session_check();
					}
						
				}

				//IF User not found in DB
				debug('$this->session_get(ALL);', $this->session_get('ALL'));
				throw new arcException ("ID: [".$this->id."] Session Data present, but no user found in DB!");
		

		//New User tp register
		} elseif ( ($this->modulename == 'register') && (@$_GET['action'] == 'doit') ){
			$this->useview = FALSE;
			
			$this->request = array_merge($_POST, $_GET);
			
			
			if (@$this->inv_mode == TRUE){
				$inv = DB_DataObject::factory('invitations');
				$inv->id_invhash = $this->request['inv_hash'];
				
				if($inv->find(TRUE) != TRUE){
					die (e('inv_id_not_valid'));
				} else {
					if ($inv->id_active != 0){
						die (e('inv_id_already_used'));
					} 
				}
			}
			
			if ( isset($this->request['username'])  && isset($this->request['colour']) && isset($this->request['captcha']) && isset($this->request['password_1']) && isset($this->request['password_2']) && isset($this->request['captchacount'])  ){

				if ( ($this->inv_mode === TRUE) || ((strtolower(trim(trim($this->request['captcha']), "\r\n")) == strtolower(captchavalue($this->request['captchacount']))) && ($this->inv_mode === FALSE)) ) {

					if ($this->request['password_1'] == $this->request['password_2']){
						if ( ($this->request['username'] != "") &&  ($this->request['colour'] != "")){
							if ($this->validate_colour($this->request['colour'])){

								$username = $this->request['username'];
								$password = $this->request['password_1'];
								$colour = $this->request['colour'];
	
								$login = $this->arc_encrypt($this->arc_hash($username), $this->arc_hash($password));
								$password = $this->arc_encrypt($this->arc_hash($password), $this->arc_hash($username));
								$colour = $this->arc_encrypt($colour, $this->arc_gen_master($this->request['password_1']));
							
								$new = DB_DataObject::factory('users');
								$new->login = $login;
								$new->password = $password;
								
								if($new->find() === 0){						
									$new->colour = $colour;
									$new->lastupdated = $this->arc_encrypt(TIME, $this->arc_gen_master($this->request['password_1']));
									
									$id = $new->insert();
									
									logit("User " . $id . " successfully registered from ". UIP);	
									$this->jail('dejail');
									
									if (@$this->inv_mode == TRUE){
										$inv->id_active = $id;
										$inv->update();
									}
									
									
									die ('1');
								
								} else {									
									logit("Register: User [".$this->request['username']."] and PW already in Database! ");
									die (e('account_already_present'));
								}

							} else {
								die (e('colour_incorrect'));
							}			
						} else {
							die (e('fields_missing'));
						}
					} else {
						 die (e('passwords_not_match'));
					}
					
				} else {
					$ret = '2';
					$tries_left = $this->jail('jail');
					if ($tries_left < 5)
						$ret = e('retry_it').' '.e('tries_left', array($tries_left), array(1));
						
					die ($ret);
				}
			} else {
				
				die (e('fields_missing'));
			}

		//FORGOT					
		} elseif ( ($this->modulename == 'forgot') && (isset($_GET['action'])) && ($_POST['username'] != '') ) {
		
			if ($this->forgot_active != TRUE){
				die(e('forgot_is_inactive'));
			}
	
			$this->useview = $this->setlayout = FALSE;

			$username = $_POST['username'];

			$forgot = DB_DataObject::factory('forgot');
			$forgot->active = 'yes';
	        $forgot->username = $this->arc_encrypt($username, $username);
		
			$ret = e('retry_it');

			if (!($forgot->find(TRUE))){
				$tries_left = $this->jail('jail');
				if ($tries_left <= 5)
					$ret .= ' '.e('tries_left', array($tries_left), array(1));

				header('Status: 403 Forbidden');
				die(e('forgot_std_no_user_msg').$ret);
			}

			if (($_POST['answer'] == '') && ($_POST['username'] != '')) {
				if ($this->arc_decrypt($forgot->username, $username) == $username){
					die($this->arc_decrypt($forgot->question, $username));
				} else {
					header('Status: 403 Forbidden');
					die(e('forgot_std_no_user_msg'));
				}

			} else {
				if ($_POST['answer'] == $this->arc_decrypt($forgot->answer, $username)){
					$hint = $forgot->hint;
					
					$forgot->lastreq = $this->arc_encrypt(TIME, $username);
					$forgot->lastreq_ip = $this->arc_encrypt(UIP, $username);
					$forgot->update();					

					logit('Passwordhint for user '.$forgot->id_users.' was successfully decrypted!');
					die($this->arc_decrypt($hint, $_POST['answer']));
				} else {

					$tries_left = $this->jail('jail');
					if ($tries_left <= 5)
						$ret .= ' '.e('tries_left', array($tries_left), array(1));
					
					header('Status: 403 Forbidden');
					die(e('forgot_std_wrong_answer').$ret);					
				} 
                        }

		//INVCHECK
		} elseif ( ($this->modulename == 'register') && ($_GET['module'] == 'inv_hash_check') ) {

			$inv = DB_DataObject::factory('invitations');
			$inv->id_inv_hash = $_GET['code'];

			die($inv->find());
		}
		
	}
	
	private function log_login() {
		$browser = explode(' ',  UAGENT);
    		$this->user_log(e('user') .' ['.$this->id.'] '. e('logged_in_from') . $browser[0]);
		
		$login_check = DB_DataObject::factory('users');	
		$login_check->id = $this->id;
		
		if ($login_check->find() === 1){				
			//store login time, ip and browser in DB
			$login_check->lastlogin = $this->arc_encrypt(TIME, $this->masterkey);
			$login_check->lastip = $this->arc_encrypt(UIP, $this->masterkey);
			$login_check->lastbrowser = $this->arc_encrypt(UAGENT, $this->masterkey);
			$login_check->update();
		}
	}
	
	
	private function validate_colour($colour){
		$ret = (preg_match($this->regex_colour, $colour)) ? TRUE : FALSE;
		return $ret;
	}


	protected function arc_check_login ($username, $password, $set_vals = FALSE) {	
		
		//logit("-----------------------------------------------------------------------------------------------");
	
		$username_hashed = $this->arc_hash($username);
		$password_hashed = $this->arc_hash($password);
	
		//logit("".$username_hashed." == ".$password_hashed);

		$login_enc = $this->arc_encrypt($username_hashed, $password_hashed);
		$password_enc = $this->arc_encrypt($password_hashed, $username_hashed);

		$login_check = DB_DataObject::factory('users');	
		$login_check->login = $login_enc;
		$login_check->password =$password_enc;

                //$test = DB_DataObject::factory('users');
                //$test->get(5);

                //logit("login58 [".utf8_decode($test->login)."]");
                //logit("pass5 [".$test->password."]");

		if ($login_check->find() === 1){
			$login_check->fetch();

			//always set ID
			$this->id = $login_check->id;
		
			//Mit einem MASTERKEY werden (fast) alle Daten in der DB verschlüsselt und entschlüsselt
			$this->masterkey = $this->arc_gen_master($password);	
			
			//always set colour
			$this->colour = $this->arc_decrypt($login_check->colour, $this->masterkey);
			
			##Col Test
			if (!($this->validate_colour($this->colour))){
				throw new arc_Exception ('colour could not have been validated');
			}

			if ($set_vals === TRUE){ //nur nach dem Login

				###############get session_ttl
                                $settings = DB_DataObject::factory('settings');
                                $settings->id_users = $this->id;

                                if ($settings->find(TRUE)){ 
                                        $lifetime = $this->arc_decrypt_output($settings->session_lifetime);
                                        if ($lifetime != ""){
                                                if (is_numeric($lifetime)){
                                                        $this->remaining_time = $lifetime;
                                                }
                                        }
                                }

				if (!($lifetime != "") || (!(@isset($lifetime))))
					$lifetime = $this->default_session_lifetime;
                                ############### 

				$this->session_starter();
				$this->set_secure_cookie($INITIAL = TRUE);
				
				//Dieser Key kommt in die Session. 
				$this->session_masterkey_set($this->masterkey);
				
				// set id after login
				$this->session_set('arc_id', $this->arc_hash($this->id));
			
				$this->session_set('logintime', TIME);				

				//crc wird in der config dynamisch gesetzt! ;)
				$this->session_set('crc', $this->crc);
			
				//lifetime in die session
				$this->session_set('lifetime', $lifetime);
				
				//set additional info to current session
				if ($login_check->lastlogin != ""){
					$this->session_set('lastlogin', $this->arc_decrypt($login_check->lastlogin, $this->masterkey));
				}
			
				if ($login_check->lastip != "")
					$this->session_set('lastip', $this->arc_decrypt($login_check->lastip, $this->masterkey));
				
				if ($login_check->lastbrowser != "")
					$this->session_set('lastbrowser', $this->arc_decrypt($login_check->lastbrowser, $this->masterkey));
				
				if ($login_check->lastupdated != "")
					$this->session_set('lastupdated', $this->arc_decrypt($login_check->lastupdated, $this->masterkey));
				
				if (filter_var(UIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))	
					$this->session_set('lastip6', UIP);

                                if (filter_var(UIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
                                        $this->session_set('lastip4', UIP);

			}

			return TRUE; //Lets go.
				
		} elseif ($login_check->find() > 1) {
			throw new arcException("Fuck. Find returned value greater 1 =>".$login_check->find());
			return FALSE;
		}	
	
		return FALSE;
	
	}
	
	private function arc_gen_master($uniqe){	
		

		$uniqe = $this->arc_hash($uniqe, '', TRUE);

                $pattern = '+[0-9]{2,3}+';
                $master = preg_replace_callback(
			$pattern,
                	create_function(
        	                '$treffer',
                                'return (strtoupper(chr($treffer[0])));'
                        ),
                        $uniqe
                );

		return ($master);
	}
	
	public function arc_hash ($hash, $salt = '', $hyperbolize = FALSE){
	
		if ($salt != '')
			logit('salt was NOT empty!');
		
		$algos = $this->used_hash_aglos;
	
		foreach ($algos as $algo) {
			$hash = hash($algo, $hash . $salt);
		}

		if ($hyperbolize){ //FUN
			$hash .= $this->arc_hash(metaphone($hash)).$this->arc_hash(crc32($hash)).$this->arc_hash(strrev($hash));
		}
		
		if ($salt == 'short')
			$hash = substr($hash, 0, 6);
		
		return ($hash);
	}
	

	protected function arcanum_change_holy ($changewhat, $changewith){
	
		$start = microtime(TRUE);

		if (($changewhat == "") || ($changewith == "")){
                        $this->session_msg(e('fields_missing'));
                        redirect("settings");
                        exit;
                }

		if (!($this->get_arc_lock())){
			$this->session_msg(e('still_in_progress'));
			redirect("settings");
		}
		
		if (PHP_OS === 'Linux'){
			$memfree = shell_exec("free -m | grep 'Mem' | awk '{print $3}'");
			if (!(is_numeric($memfree)))
				$memfree = $this->min_mem + 1;
			$load = sys_getloadavg();
	
			if ( ($memfree < $this->min_mem) || ($load[0] > $this->max_load) ){
				$this->session_msg(e('load_to_high')." LOAD: $load [max. $this->max_load], MEM $memfree [min. $this->min_mem]M");
				
				$this->del_arc_lock();
				redirect("settings");
				exit;
			}
		}
	
		if (($changewhat == 'password') || ($changewhat == 'colour')){
								
				if ($changewhat == 'password') {
					
					if ($this->arc_check_login($this->request['username'], $this->request['password_old'])) {
						
						$new_holy = array();

						$new_holy['masterkey'] = $this->arc_gen_master($changewith);
						
						if ($this->masterkey == $new_holy['masterkey']){							
							$this->session_msg(e('password') . ' - ' . e('no_changes'));
							redirect("settings");
							exit;
						}
						
					} else {
							$this->session_msg(e('credentials_false'));
							redirect("settings");
							exit;
					}
						
				} elseif ($changewhat == 'colour') {
					
					if ($this->validate_colour($changewith)){
						$new_holy = array();
						$new_holy['colour'] = $changewith;
					} else {
						$this->session_msg(e('colour_incorrect'));
						redirect("settings");
					}
					
					if ($this->colour == $new_holy['colour']){
						$this->session_msg(e('colour') . ' - ' . e('no_changes'));
						redirect("settings");
						exit;
					} 
						
				}
			
				ignore_user_abort(true);	
				$this->put_arc_lock(e('preparing'));
				$this->put_arc_lock(TRUE);

				#FUN #######################################################################################
				
				if (!(is_array($new_holy))){
					throw new arcException("epic fail");
					exit;
				}
				
				if ((array_key_exists('colour', $new_holy))  && (array_key_exists('masterkey', $new_holy))){
					$this->session_msg(e("change_only_one"));
					redirect("settings");
					exit;
				}
				
				$this->put_arc_lock(e('reencrypt_categories'));
				
				//1////// --->  STEP: categories
				$categories = DB_DataObject::factory('categories');
				$categories->id_users = $this->id;

		##############################
		$conn = & $categories->getDatabaseConnection();
		$conn->autocommit(FALSE);
		$connect_erros = 0;		
		###########
		
		/* http://markmail.org/message/574r4lr6n2offqpd#query:+page:1+mid:ckskwoejkqcwmq6e+state:results
		 I'm gonna summarize the conclusion:
		DB_DataObject and transactions work great. The following example is
		tested and it works fine. If a query fails or the connection
		disconnects (see commented line), $errors is greater than zero and you
		can roll it back.


		$errors = 0;


		$person = DB_DataObject::factory('person');


		$conn = & $person->getDatabaseConnection();
		$conn->autocommit();


		$mom = DB_DataObject::factory('mom');
		$mom->name = 'Mary';
		$mom_id = $mom->insert();
		if (!$mom_id) $errors++;


		//$conn->disconnect();


		$person->name = 'Fred';
		$person->mom_id	= $mom_id;
		$res = $person->insert();
		if (!$res) $errors++;


		//do some stuff, more queries


		if ($errors == 0) $conn->commit();
		else $conn->rollback();
		*/

				$cats = array();
				if ($categories->find()){
					while($categories->fetch()){
						$cats[] = $categories->id;					
						
						$new_categories = $this->arc_encrypt_input(   $this->arc_decrypt_output(array(clone($categories)), TRUE)   , FALSE, $new_holy);
						$check = $new_categories->update();
						
						if (!$check) $connect_erros++;						
					}
				
		
	
					//2///// --->  STEP: count for files
					$filescount = 0;
					foreach ($cats as $cat){
						$files = DB_DataObject::factory('files');
						$files->id_categories = $cat;
						
						$filescount = $files->find() + $filescount;
					}
				}

				$i=1;
				foreach ($cats as $cat){
					//2///// --->  STEP: files
					$files = DB_DataObject::factory('files');
					$files->id_categories = $cat;
					
					$countfiles = $files->find();
					if ($countfiles){
						while($files->fetch()){
							$this->put_arc_lock(e('reencrypt_files').' ['."$i / $filescount".']');
							$new_files = $this->arc_encrypt_input(   $this->arc_decrypt_output(array($files), TRUE)   , FALSE, $new_holy);
							$check = $new_files->update();

							if (!$check) $connect_erros++;

							$i++;	
						}
							
					}
					unset($files);#Perf
					
					//3///// --->  STEP: portals
					$portals = DB_DataObject::factory('portals');
					$portals->id_categories = $cat;
					if ($portals->find()){
						
						$this->put_arc_lock(e('reencrypt_portals'));
						
						while($portals->fetch()){
							$ports[] = $portals->id;
							$new_portals = $this->arc_encrypt_input(   $this->arc_decrypt_output(array(clone($portals)), TRUE)   , FALSE, $new_holy);
							$check = $new_portals->update();

							if (!$check) $connect_erros++;							
						}
							
					}
					
						
				}
				
				if (isset($ports)){
					$this->put_arc_lock(e('reencrypt_arcanums'));
					foreach ($ports as $port){
						//4///// --->  STEP: arcanums
						$arcanums = DB_DataObject::factory('arcanums');
						$arcanums->id_portals = $port;
						if ($arcanums->find()){
							while($arcanums->fetch()){
								$new_arcanums = $this->arc_encrypt_input(   $this->arc_decrypt_output(array(clone($arcanums)), TRUE)   , FALSE, $new_holy);
								$check = $new_arcanums->update();
								if (!$check) $connect_erros++;								
							}
							
						}
					}
				}
				
				//5///// --->  STEP: settings
				$this->put_arc_lock(e('reencrypt_settings'));
				$settings = DB_DataObject::factory('settings');
				$settings->id_users = $this->id;
				$settings->find(TRUE);
				
				$new_settings = $this->arc_encrypt_input(   $this->arc_decrypt_output(array(clone($settings)), TRUE)   , FALSE, $new_holy);
				$check = $new_settings->update();
				if (!$check) $connect_erros++;
				
				//5.5 ---> STEP: LOG
				$this->put_arc_lock(e('reencrypt_logs'));
				$log = DB_DataObject::factory('log');
				$log->id_users = $this->id;
				if ($log->find()){
					while($log->fetch()){
						$new_log = $this->arc_encrypt_input(   $this->arc_decrypt_output(array(clone($log)), TRUE)   , FALSE, $new_holy);
						$check = $new_log->update();	
						if (!$check) $connect_erros++;						
					}
						
				}
				
				//5.75 ---> STEP: INVATIONS
				$this->put_arc_lock(e('reencrypt_invs'));
				$invs = DB_DataObject::factory('invitations');
				$invs->id_users = $this->id;
				if ($invs->find()){
					while($invs->fetch()){
						$new_inv = $this->arc_encrypt_input(   $this->arc_decrypt_output(array(clone($invs)), TRUE)   , FALSE, $new_holy);
						$check = $new_inv->update();				
						if (!$check) $connect_erros++;
					}
				
				}


                                //5.8 ---> STEP: MEMO
                                $this->put_arc_lock(e('reencrypt_memos'));
                                $memos = DB_DataObject::factory('memos');
                                $memos->id_users = $this->id;
                                if ($memos->find()){
                                        while($memos->fetch()){
                                                $new_memo = $this->arc_encrypt_input(   $this->arc_decrypt_output(array(clone($memos)), TRUE)   , FALSE, $new_holy);
                                                $check = $new_memo->update();
                                                if (!$check) $connect_erros++;
                                        }

                                }

			
				if ($connect_erros == 0){
	
					//6///// ---> LAST STEP: users
					$this->put_arc_lock(e('reencrypt_you'));
					$users = DB_DataObject::factory('users');
					$users->get($this->id);

					if ($changewhat == 'password'){
					
						$users->login = $this->arc_encrypt($this->arc_hash($this->request['username']), $this->arc_hash($changewith));
						$users->password = $this->arc_encrypt($this->arc_hash($changewith), $this->arc_hash($this->request['username']));					
					
						$users->colour = $this->arc_encrypt($this->colour, $new_holy['masterkey']);
						
						$users->lastlogin = $this->arc_encrypt($this->arc_decrypt($users->lastlogin, $this->masterkey), $new_holy['masterkey']);
						$users->lastip = $this->arc_encrypt($this->arc_decrypt($users->lastip, $this->masterkey), $new_holy['masterkey']);
						$users->lastbrowser = $this->arc_encrypt($this->arc_decrypt($users->lastbrowser, $this->masterkey), $new_holy['masterkey']);
					
						$users->lastupdated = $this->arc_encrypt(TIME, $new_holy['masterkey']);
					
						$this->session_masterkey_set($new_holy['masterkey']);
						$this->session_set('lastupdated', TIME);
						$this->session_msg(e('password_changed_successfully'));

					
					
					} elseif  ($changewhat == 'colour') {
					
						$users->colour = $this->arc_encrypt($changewith, $this->masterkey);
					
						$this->session_msg(e('colour_changed_successfully'));
					}				
					$check = $users->update();
					if (!$check) $connect_erros++;				
				}				
				################################################################################################# FUN End.
	
				$log = "User " . $this->id . " changed ". $changewhat. ' => ';
				if ($connect_erros == 0) {
					$conn->commit();					#THIS
					$this->del_arc_lock();
					$log .= "Action Commited, no errors";
		
					//logit($log);
					redirect("settings");
	
				} else {
                                        $conn->rollback();					#THIS
					$this->del_arc_lock();
					$log .= "Action rolled back, error count is [".$connect_erros."]";
					throw new arcException($log);
				}
			##//END STUFF
				
				

			
		} elseif ($changewhat == 'username') { //CHANGE USERNAME ?
			
			if ($this->arc_check_login($this->request['username'], $this->request['password_old'])){
					
				$users = DB_DataObject::factory('users');
				$users->id_users  = $this->id;
				$users->find(TRUE);
				
				$new_users->login  = $this->arc_encrypt($this->arc_hash($changewith), $this->arc_hash($this->request['password']));
				$new_users->update();
			} else {
				$this->session_msg(e('credentials_false'));
				redirect("settings");
				exit;
			}
		}
	}


	private function mem_usage_test () {
		$memusage = memory_get_usage(TRUE);

		if (isset($this->mem)){
			$this->mem = ($this->mem < $memusage) ? $this->mem : $memusage;
		} else {
			$this->mem = $memusage;
		}
	}
	
	protected function get_arc_lock (){
		if(!($this->check_arc_lock())){
			return (touch($this->tmppath . $this->arc_hash($this->id)));
		} else {
			return FALSE;
		}
	}
	
	protected function check_arc_lock (){
		return (file_exists($this->tmppath . $this->arc_hash($this->id)));
	}
	
	protected function put_arc_lock($text){
	
		if ($this->check_arc_lock()){
			if ($text === TRUE){
				return (file_put_contents($this->tmppath . $this->arc_hash($this->id), " ." , FILE_APPEND));
			} else {
				return (file_put_contents($this->tmppath . $this->arc_hash($this->id), $text));
			}
				
		} else {
			return FALSE;
		}
	}
	
	protected function del_arc_lock (){
		if($this->check_arc_lock()){
			unlink($this->tmppath . $this->arc_hash($this->id));
		} else {
			return FALSE;
		}
	}

	private function clean_tmp () {
		$files = scandir($this->tmppath);

		foreach ($files as $filename){
			$file = $this->tmppath.$filename;
			if ( ($filename != '..')  && ($filename != '.') && ((filectime($file)) < (TIME - $this->tmp_clean_time)) ){
				if (dirname($file) == substr($this->tmppath, 0, -1)){			
					unlink($file);
					logit('deleted file ['.$file.']');
				} else {
					logit('config problem, I wanted to delete file ['.$file.']');
				}
			}
		}
	}
	
	/*
	 * 
	 * passage to VIEW
	 * 
	 */
	
	
	
	public function dispatch (){
	
		if (isset($this->data)){
			$this->data = $this->arc_decrypt_output($this->data);
		}		

		if (@$this->session_msg != ""){
			$this->user_log($this->session_msg);
		}
		
		if ($this->session_exists('msg')){
			$this->msg = $this->session_get('msg');
			$this->session_del('msg');
		} else {
			$this->msg = FALSE;
		}
		
		if ($this->msg != ""){
			$this->user_log($this->msg);
		}
		
		if ($this->useview == FALSE) {
			ob_end_clean();
			echo $this->content;
			exit;
		}		
		
		//if ($this->request['xload'])
			//$this->setlayout = FALSE;
		
		if ($this->setlayout && ($this->modulename != 'login')){			
			$this->content['timer_display_content'] = $this->time_left_display();
	
			$this->session_regenerate();
			
			if ( (defined('DEBUG_ON')) && ($this->modulename != 'login') ){
				debug(e('your_session'), $_SESSION);
				$debug = ob_get_contents();				
			} 
			
			ob_end_clean();
						
		} else {
			ob_end_clean();
			
			if ((!(isset($this->data))) && ($this->action != 'logout')) 
				echo $this->content;
		}	

		$dispatcher = new view($this->setlayout);
		$dispatcher->set($this->modulename, $this->content, $this->data, $this->msg);
		$dispatcher->display();
		
		if (defined('DEBUG_ON')){
			echo '<div id="debug">';
			echo '<h2> ----------------- DEBUGGING BEGIN ----------------- </h2>';
			echo 'Your ID: ' . $this->id .'<br>';
			echo 'Mem peak usage: ' . memory_get_peak_usage(TRUE)/1000/1000 . 'M.';
			echo '<br>';
			echo (defined('HIDE_COMMENT'))?'comments are hidden':'comments are shown';
			echo '<br>';
			echo (defined('HIDE_DESC'))?'descs are hidden':'descs are shown';
			echo '<br>';
			if (isset($debug))
				echo $debug;			

			echo "<h4>MASTERKEY</h4>[".$this->masterkey."]<h4>MASTERKEY</h4>";
			echo "<h2> ----------------- DEBUGGING END ----------------- </h2>";
			echo '</div>';
		}
		
	}
	
	


	/*
	 * 
	 * DE - AND ENCRYPTING
	 * 
	 */

	

	protected function arc_decrypt_output($content, $returnobjets = FALSE){

		//debug("this your encrypted content", $content); //output decrypted values

		if ( is_array($content) || is_object($content) ){
			$dec_content = array();
			foreach ($content as $num => $entry){
				if ( is_array($entry) || is_object($entry) ){
					foreach ($entry as $key => $arcanum){
						if (!(preg_match('%^id_?|^__?|^N$|users_id%', $key))){							
							
							echo "encrypting: " . $key;
							
							if ($returnobjets){
								$content[$num]->$key = $this->arc_decrypt($arcanum, $this->masterkey, $this->colour);
							} else {
								$dec_content[$num][$key] = $this->arc_decrypt($arcanum, $this->masterkey, $this->colour);
							}

						}				
					}
				}
			}
		
		} else {
			$dec_content = '';			
			$dec_content = $this->arc_decrypt($content, $this->masterkey, $this->colour);
		}
		

		if ($returnobjets){
			return ($content);
		} else {
			return ($dec_content);
		}
	}
	
	protected function to_array($obj){		
		
		$arr = array();
		foreach ($obj[0] as $key => $val){
			if (!(preg_match('%^id_?|^__?|^N$|users_id%', $key))){
				$arr[$key] = $val;
			}
		}
		
		return ($arr);
	}
	
	protected function arc_encrypt_input($dbobject, $newvalues = "", $new_holy_vals = FALSE){
	
		$masterkey = $this->masterkey;
		$colour = $this->colour;		
		
		if ($newvalues === FALSE){ //if user changes pw or col
			
			$newvalues = $this->to_array($dbobject);
			//debug("new_vals_for_change_holy", $newvalues);
			
			if (is_array($new_holy_vals)){
				$dbobject = $dbobject[0];
				if (array_key_exists('masterkey', $new_holy_vals)) {
					$masterkey = $new_holy_vals['masterkey'];
				} elseif (array_key_exists('colour', $new_holy_vals)) {
					$colour = $new_holy_vals['colour'];
				} else {
					throw new arcException('new_holy_not_array for User ' . $this->id . ' action was ' . $this->request['module']);
				}
			}
			
		} elseif ($newvalues == ""){ //DEFAULT
			$newvalues = $this->request;
		}
		
		//debug("this your OLD (non-encrypted) object", $dbobject); //output input values
	
		if ( is_array($dbobject) || is_object($dbobject) ){
			
			$dbobject_enc = $dbobject;			
			foreach ($newvalues as $key => $arcanum){
				if (!(preg_match('%^id_?|^__?|^N$|users_id%', $key))){							
					if ( ($arcanum != "") &&  property_exists($dbobject, $key) ) {
						$dbobject_enc->$key = $this->arc_encrypt($newvalues[$key], $masterkey, $colour);
					}
				} else {
					$dbobject_enc->$key = $newvalues[$key];
				}				
			}
			
		} else {
			
			$dbobject_enc = "";
			$dbobject_enc = $this->arc_encrypt($dbobject, $masterkey, $colour);
		}
		
		
		//debug("this your NEW (encrypted) object", $dbobject_enc); //output input values
		
		return ($dbobject_enc);
	}


	
	
	/*
	* Function for MY ARCANUMS
	*/

        protected function get_mcrypt_thing($intv, $ciph_lenght){
                while (strlen($intv) <= $ciph_lenght){ $intv = $this->arc_hash($intv); }
                if (strlen($intv) > $ciph_lenght) { $intv = substr($intv, 0, $ciph_lenght); }
                return ($intv);
        }

	protected function arc_encrypt ($arcanum, $password, $intvector = FALSE, $max_ciph = 0, $raw = TRUE)  {
		
		$arcanum = (gzdeflate($arcanum, 9));
		
		//Should be set, except for login check
		$intvector ? $intvector = $intvector : $intvector = $password;

                foreach ($this->used_mcrypt_ciphers as $ciph => $mode) {
	                
			$arcanum = mcrypt_encrypt($ciph, $this->get_mcrypt_thing($password, mcrypt_get_key_size($ciph, $mode)), $arcanum, $mode, $this->get_mcrypt_thing($intvector, mcrypt_get_iv_size($ciph, $mode)));
			if ($arcanum == FALSE)
				throw new arcException(' User['.$this->id.']@['.$this->modulename.']' ."\n". 'USER ('.$this->id.') - mcrypt returned FALSE while encrypting', 408);
			
        	        $arcanum = ($base64) ? base64_encode($arcanum) : $arcanum;

                }
                        
                $arcanum = (gzdeflate($arcanum, 9));
	        return ($arcanum);
	}

	

	protected function arc_decrypt ($arcanum, $password, $intvector = FALSE, $max_ciph = 0, $raw = TRUE) {
			
			if ($arcanum == '')
				return $arcanum;
		
			$we_want_decrypt = $arcanum;
		
			$arcanum = gzinflate($arcanum);

			//Should be set, except for login check
			$intvector ? $intvector = $intvector : $intvector = $password;
	
			foreach (array_reverse($this->used_mcrypt_ciphers, TRUE) as $ciph => $mode) { //Fun. -> anti-clockwise
				
				$arcanum = ($base64) ? base64_decode($arcanum) : $arcanum;
				$arcanum = mcrypt_decrypt($ciph, $this->get_mcrypt_thing($password, mcrypt_get_key_size($ciph, $mode)), $arcanum, $mode, $this->get_mcrypt_thing($intvector, mcrypt_get_iv_size($ciph, $mode)));
			}

		        $arcanum_ret = gzinflate($arcanum);
	        	
			if (($arcanum == FALSE) && (!(($this->module == 'export') && ($this->action == 'importall'))) ) {
                                throw new arcException(' User['.$this->id.']@['.$this->modulename.']' ."\n". 'USER ('.$this->id.') - mcrypt returned FALSE while decrypting', 408);
			} elseif ($arcanum_ret == FALSE){
	  			throw new arcException(' User['.$this->id.']@['.$this->modulename.']' ."\n". 'USER ('.$this->id.') - gzinflate returned FALSE for val ['.trim(utf8_encode($we_want_decrypt)).']', 408);
			} else {
	  			$arcanum = $arcanum_ret;
	  		}
			
	  	if (isset($this->colour) && (defined('DEBUG_ON'))){
			if (strlen($arcanum) < 1000){
			        echo "<b class='info'>successfully decrypted </b>[" . $arcanum ."] from [" .$we_want_decrypt. "]<br>";
			} else {
				echo '<b class="info">successfully decrypted </b>[FILE_OBJECT] from [strlen($arcanum) < 1000]<br>';
			}
		}
	        return ($arcanum);
	}
	

	
	############## ::MISC #################
	
	
	/*
	* Function for Hashings
	*
	*/
	
	//SESSION FUNCTIONs
	
	public function kill () {
		setcookie ($this->cookiename, '', TIME - 3600);
		session_regenerate_id(TRUE);
		$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
		session_unset();
		session_destroy();
	}
	
	private function session_starter (){
	
		/* setzen der Cacheverwaltung auf 'private' */
		session_cache_limiter('nocache');
		// $cache_limiter = session_cache_limiter();
	
		/* starten der Session */
		$currentCookieParams = session_get_cookie_params();
		session_set_cookie_params($currentCookieParams["lifetime"], substr($this->relpath,0,-1), $_SERVER['HTTP_HOST'], $this->https, TRUE);
		session_start();

                $sessionlifetime = ($this->session_exists('lifetime')) ? $this->session_get('lifetime') : $this->default_session_lifetime;
	
		/* setzen der session lifetime */
		ini_set("session.gc_maxlifetime", $sessionlifetime);
	}
	
	
	private function check_cookies (){
		if ($this->session_exists('redirect_logged_in')){
			$this->session_set('redirect_logged_in', $this->session_get('redirect_logged_in')+1);
			if ($this->session_get('redirect_logged_in') > 4){				
				$this->user_log(e('session_timeout') . ' - browserrestart?');
				$this->kill();
				define(SESSION_TIMEOUT_LOGOUT, TRUE);
			}
		} else {
			$this->session_set('redirect_logged_in', 1);
		}
	}

	private function session_check () {
		if ($this->session_get('crc') === $this->crc){
	
			if ($this->arc_hash(serialize($_SESSION[$this->session_key])) === $this->get_session_hash()){
				echo '<br><br><h3 class="info">session hash check succeeded.</h3><br>';
			} else {
				throw new arcException('session hash check failed, someone bastelt?', 666);
			}
		
			if ($this->file_integrity_check == TRUE) {	
				if ($this->session_get($this->session_integrity_checksum) == sha1_file(__FILE__)){
					echo '<br><br><h3 class="info">file integrity check succeeded.</h3><br>'; 
				} else {
					echo '<br><br><h3 class="error">file integrity check FAILED</h3><br>';
					throw new arcException('file integrity check failed, change time is ' . strftime($this->dateopts_log, filectime(__FILE__)), 666); 
				}	
			}

			return $this->check_secure_cookie();
		}
		return FALSE;
	}
	
	protected function set_secure_cookie ($INITIAL = FALSE) {

		if (headers_sent() === FALSE){
		
			//random
			$session_content = $this->get_random_val();
			//random

			#logit('Generated new cookie hash for ID '.$this->id . '['.$session_content.']');
			
			$cookiecontent = $this->arc_encrypt_session_var($session_content);
			$this->session_set('random_number', $session_content);
			$this->random_number_once = $session_content;
			
			$remaining_time = (isset($this->remaining_time)) ? TIME + $this->remaining_time : TIME + ini_get('session.gc_maxlifetime');

			$remaining_time += 600;

			if ($INITIAL == FALSE)
				$this->session_masterkey_regenerate();

			setcookie($this->cookiename, $cookiecontent, $remaining_time, substr($this->relpath,0,-1), $_SERVER['HTTP_HOST'], $this->https, TRUE);
		
		} 
	}

	private function session_regenerate () {
		
		if (!($this->session_exists('request_counter')))
			$this->session_set('request_counter', 1);
		
		$request_counter = $this->session_get('request_counter');
		
		if ( (($request_counter % $this->session_regenerate_frequency) == 0) && ($request_counter > 0) ){
			$this->set_secure_cookie(); //dynamically changing cookie deactivated
			session_regenerate_id(TRUE);
			$request_counter=0;
		}
		
		$this->session_set('request_counter', $request_counter+1);		
	}

	private function check_secure_cookie () {
		$session_crypt_var = $this->session_get('random_number');
		$cookie_crypt_var = $this->arc_decrypt_session_var($_COOKIE[$this->cookiename]);		

		if ($session_crypt_var == $cookie_crypt_var){
			echo '<br><br><h3 class="info">COOKIE CHECK OK</h3><br>';
			return TRUE;
		} else {
			throw new arcException('Cookie CHECK FAILED for '.$this->id.' session var was ['.$session_crypt_var.'] !eq cookie var['.$cookie_crypt_var.']');
			return FALSE;
		}
	}
	
	private function get_session_hash(){
		return $_SESSION[$this->session_hashkey];
	}
	
	private function set_session_hash(){
			
		if (isset($_SESSION[$this->session_key])){
		
			$this->session_set($this->session_integrity_checksum, sha1_file(__FILE__));

			$hash = serialize($_SESSION[$this->session_key]);
			$_SESSION[$this->session_hashkey] = $this->arc_hash($hash);

			return TRUE;
		}
		return FALSE;	
	}
	
	//http://phpsec.org/projects/guide/4.html
	
	protected function session_msg ($msg) {
		$this->session_set('msg', $msg);
	}
	
	protected function session_set ($key, $val) {
		$value = $this->arc_encrypt_session_var($val);
		$key = $this->arc_encrypt_session_var($key);
		
		$_SESSION[$this->session_key][$key] = $value;
	}

	protected function session_get ($key) {

		$SESSION_OPENEND = FALSE;	

		if (!(isset($_SESSION[$this->session_key]))){
			$this->session_starter();
			$SESSION_OPENEND = TRUE;
		}			
	
		if ( $key === "ALL" ){
			return ($_SESSION[$this->session_key]);
		}
	
		$name = $this->arc_encrypt_session_var($key);		

		
		if (isset($_SESSION[$this->session_key][$name])){
			$value = $this->arc_decrypt_session_var($_SESSION[$this->session_key][$name]);
			return $value;
		}
		
		if ($SESSION_OPENEND === TRUE)
			$this->kill();

		return FALSE;
    }

	private function arc_encrypt_session_var ($var) {
		return $this->arc_encrypt($var, $this->get_session_crypt_key());
	}

	private function arc_decrypt_session_var ($var) {
        	return $this->arc_decrypt($var, $this->get_session_crypt_key());
	}

        private function get_session_crypt_key ($crypt_var = FALSE) {
                if (!(isset($this->session_cryptkey))) {
                        $this->session_cryptkey = '';
                        foreach ($this->session_encrypt_env_vars as $cryptvar){
                                $this->session_cryptkey .= (isset($_SERVER[$cryptvar])) ? $this->arc_hash($_SERVER[$cryptvar]): '';
                        }
                }
                return ($this->session_cryptkey);
        }

    
    protected function session_del ($key) {
    
    	$name = $this->arc_encrypt_session_var($key);
    
    	if (isset($_SESSION[$this->session_key][$name])){
    		unset($_SESSION[$this->session_key][$name]);
    		return TRUE;
    	}
    
    	return FALSE;
    }
    
    protected function session_exists ($key) {
    
    	$name = $this->arc_encrypt_session_var($key);
    
    	if (isset($_SESSION[$this->session_key][$name])){
			return TRUE;
    	}
    
    	return FALSE;
    }

	###################################################################
	
	private function session_masterkey_set ($masterkey) {
		
		$key = (isset($this->random_number_once)) ? $this->random_number_once : $this->arc_decrypt_session_var($_COOKIE[$this->cookiename]);
		$this->session_set('arc_master_key', $this->arc_encrypt($masterkey, $key));
		#$this->session_set('arc_key',$masterkey);
	}

	private function session_masterkey_get ($old_one = FALSE) {


		$key = (isset($this->random_number_once)) ? $this->random_number_once : $this->arc_decrypt_session_var($_COOKIE[$this->cookiename]);
		
		if (!(isset($_COOKIE[$this->cookiename])))
			logit('!!!!!!!!!!!!! This Cookiename was empty [' . $this->id . ']!!!!!!!!!!!!');

		return $this->arc_decrypt(($this->session_get('arc_master_key')), $key);
	}

	private function session_masterkey_regenerate () {
		$this->session_masterkey_set($this->arc_decrypt(($this->session_get('arc_master_key')), $this->arc_decrypt_session_var($_COOKIE[$this->cookiename])));		
	}
    
	###################################################################
	
	private function jail($cmd) {
    	
    	if ($this->jail_max_tries != 0){
	    	
    		$jail = DB_DataObject::factory('jail');
    		$ip = md5(UIP . UAGENT);
    	

		if (is_array($this->jail_whitelist)) {
			if (in_array(UIP, $this->jail_whitelist))
				return FALSE;
		}
	
	    	switch ($cmd){
	    		
	    		case 'jail':
	    			
	    			$jail->ip = $ip;
	    			
	    			if ($jail->find()){
	    				$jail->fetch();
	    				$jail->tries++;
	    				$jail->time = TIME;
	    				$jail->update();
						
						if ($jail->tries == $this->jail_max_tries)
							logit("IP blocked because of ".$jail->tries. " failed logins");
	    			
	    			} else {
	    				$jail->tries = 1;
	    				$jail->time = TIME;
	    				$jail->insert();
	    			}
	    			
	    			return ($this->jail_max_tries - $jail->tries);
	    			logit(UIP . " is jailed");			
	    			break;
	    			
	    		case 'dejail':
	    			$jail->ip = $ip;
	    			
	    			if ($jail->find()){
	    				$jail->fetch();
	    				$jail->delete();
	    				
	    				//logit(UIP . " is de-jailed, was nominated to block");
	    			}
	    			
	    			break;
	    			
	    			
	    		case 'clean_and_check':
	    			
	    			$last_jail = TIME - $this->jail_time;


				if (PEAR::isError($jail)) {
					die($jail->getMessage().' '.$jail->getUserInfo());		
				}
	
	    			$jail->whereAdd("time <= $last_jail");
	    			
	    			if ($jail->find()){
	    				while($jail->fetch()){
	    					$jail->delete();							
	    					logit(UIP . " is de-jailed, was blocked for ".$this->jail_time." seconds");
	    				}
	    			}    			
	    			
	    		case 'check':
	    			
	    			$jailcheck = DB_DataObject::factory('jail');
	    			$jailcheck->ip = $ip;
	    				 
	    			if ($jailcheck->find()){
	    				$jailcheck->fetch();
	    			}
	    				 
	    			$tries_left = $this->jail_max_tries - $jailcheck->tries;
	    				 
	    			if ($tries_left <= 0){

						header("HTTP/1.0 403 Forbidden", TRUE, 403);
						
						$remaining_time = round(($this->jail_time - (TIME - $jailcheck->time))/60, 0);
						$hint = ($this->forgot_active === TRUE) ? e('jailed_use_forgot_hint') : '';

						$this->view_fallback('JAILED', e('jailed', array($this->jail_max_tries, $remaining_time), array(1,2)), $hint);
	    			}
	    			
	    			break;
	    			
	    			
	    		default:
	    			return FALSE;
	    	
	    	}    		
    	}
    
    	
    }

    public function view_fallback ($reason, $content, $msg) {
		define('VIEW_FALLBACK', $reason);

		$dispatcher = new view($this->setlayout);
        $dispatcher->set('fallback', $content, '', $msg);
        $dispatcher->display();
		exit;
    }
    
    public function user_log ($text, $logger = "USER", $id = FALSE, $stamp = FALSE, $ip = FALSE){
    		
    	$id = (isset($this->id)) ? $this->id : $id;
		
		if ($id == FALSE)
			return $id;

		$log = DB_DataObject::factory('log');
		$log->id_users = $id;
		
		$time = (is_numeric($stamp)) ? $stamp : TIME;		
		
		$text = ($logger != "USER") ? '['.$logger.']'.$text : $text;
		
		$ip = ($ip != FALSE) ? $ip : UIP;
	
		$save_log = $this->arc_encrypt_input($log, array('time' => $time, 'ip' => $ip, 'log' => $text));
		$save_log->insert();
    }
    
    protected function user_log_cleanup (){
    	$log = DB_DataObject::factory('log');
    	$log->id_users = $this->id;
    	
    	$DELETION = FALSE;
    	if ($log->find()){
    	
    		while($log->fetch()){
    			$logs[$log->id] = $this->arc_decrypt_output(array(clone($log)));
    			
    			if ( $logs[$log->id][0]['time']  <  (TIME - ($this->logstoretime_days*24*60*60)) ){
    				$DELETION = TRUE;
    				$log->delete();
    			}
    		}
    			
    	}
    	if ($DELETION){
    		$this->user_log(e('auto_log_cleanup', array($this->logstoretime_days)), $this->systemlogger);
    	}    	
    }
    
    protected function user_invitations_cleanup (){
    	$inv = DB_DataObject::factory('invitations');
    	$inv->id_users = $this->id;
    	$inv->id_active = 0;
    	 
    	$DELETION = FALSE;
    	if ($inv->find()){
    		 
    		while($inv->fetch()){
    			$invs[$inv->id] = $this->arc_decrypt_output(array(clone($inv)));
    			 
    			if ( $invs[$inv->id][0]['time']  <  (TIME - ($this->inv_valid_time_days*24*60*60)) ){
    				$DELETION = TRUE;
    				$DELETEDINVS[] = $invs[$inv->id][0]['receipient'];
    				$inv->delete();
    			}
    		}
    		 
    	}
    	if ($DELETION){
    		foreach ($DELETEDINVS as $delreceipient){
    			$this->user_log(e('auto_inv_cleanup', array($delreceipient, $this->inv_valid_time_days)), $this->systemlogger);
    		}
    	}
    }


	protected function time_left_display() {

                $sessionlifetime = ini_get('session.gc_maxlifetime');
                $time = $sessionlifetime - (TIME - $this->session_get('logintime'));

                $time_in_min = $time/60;
                $time_in_h = $time_in_min/60;

                $h = floor($time_in_h);
                $min_comma = $time_in_h - $h;
                $min = floor($min_comma*60);

				$time_r = '';

				
				if (defined('SESSION_TIMEOUT_LOGOUT')) {
					$time_r .= '
						<script type="text/JavaScript">
							window.location = \''.link_for('login','logout').'&msg='.serialize(e('session_timeout')).'\'
						</script>
					';
				}
			
                if ($time_in_h >= 1){
                        $time_r .= $h . ":" . $min . " h";
                } elseif ($time <= 61) {
                        $time_r .= $time . ' <b style="color:red;">s</b>';
                } elseif ( $min <= 60 ) {
                        $time_r .= $min . ' <b style="color:darkred;">m</b>';
                } elseif ( $min <= 5 ) {
                        $time_r .= $min . ' <b style="color:red;">m</b>';
                }

		return $time_r;
	}

	public function get_random_val () {
		
		list($usec, $sec) = explode(' ', microtime());
                $seed = (float) $sec + ((float) $usec * 100000);
                mt_srand($seed);
		$n = mt_rand();

		$bytes = mcrypt_create_iv(2048, MCRYPT_DEV_URANDOM);
		$val = $bytes;

		$ret = (CRYPT_SHA512 == 1) ? crypt($val, $n) : $this->arc_hash($val.$n);
		return (hash('SHA512', $ret));

	}
    /*
     * END MAIN CLASS
     */
}
