<?php

class view {
	
	public $templatepath;
	public $layoutpath;
		
	public $modulename;
	public $response;
	
	private $layout;
	private $menu;
	private $content;
	
	private $not_authenticated;
	
	private $output;
	
	public $crc;
	
	public function __construct($getlayout = TRUE){
		
		global $config;
		
		$this->request = array_merge($_POST, $_GET);
		if (!(isset($this->request['action'])))
			$this->request['action'] = 'show';
		
		foreach ($config as $confvar => $confval){		
				$this->$confvar = $confval;
		}
		$this->getlayout = $getlayout;
		
	}
	
	public function not_authenticated ($msg = "", $request){
		
		$this->not_authenticated = TRUE;
		
		if (in_array($request['module'], $this->registered_modules_no_auth)) {
		
			foreach ($request as $key => $val){
		    	if (count($val) > $this->max_input_length_no_auth)
                	throw new arcException("non auth user submitted [$val] for '$key' and it was longer than ".$this->max_input_length_no_auth);
		        
			}
		
			$this->set($request['module'], $msg);
		
		} else {

			redirect('login');
		}
	}
	
	
	public function set($modulename, $content = "", $data = array(), $msg = FALSE) {
		
		$this->modulename = $modulename;		
		$this->template = $this->templatepath . $this->modulename . ".php";	
		
		$sessionmessage = FALSE;		
		if ($msg)
			$sessionmessage = $msg;
		
		if ($modulename == 'getcaptcha'){
			require_once $this->template;
			die();
		}
		
		ob_start();
				
		//TEMPLATE
		if ( file_exists($this->template) ){
			require_once $this->template;
		} else {
			echo $content;
		}		
		$this->content = ob_get_contents();
		ob_end_clean();
		
				
		if ($this->getlayout){
			
			if ((!(isset($this->not_authenticated))) && ($modulename != "login")){					
				$auth = TRUE;
			}
			
			if ($modulename == 'fallback')
				$auth = FALSE;
			
			//MOVED TO HERE ;)
			if (file_exists($this->layoutpath)){
				ob_start();
								
				require_once $this->layoutpath;		
				$this->layout = ob_get_contents();				
				ob_end_clean();
				
			} else {				
				throw new arcException ('Problem with layout!');				
			}
			
			//AJAXGET
			if  ($this->modulename == 'ajaxget') {									
				ob_start();
				switch ($this->request['action']){
					case 'menu':
						if (file_exists($this->menupath)){
							include_once $this->menupath;
						}
						break;
						
					case 'actionpanel':
						if (file_exists($this->actionpanelpath)){
							include_once $this->actionpanelpath;
						}							
						break;
						
					default:
						throw new arcException ('Problem with ajaxget!');
						break;
				}				
				$this->output = ob_get_contents();
				ob_end_clean();
				return TRUE;
			} //AJAXQUICKFIX
		
			//ACTIONPANEL
			if (file_exists($this->actionpanelpath)) {				
				ob_start();
				include_once $this->actionpanelpath;
				$this->actionpanel = ob_get_contents();
				ob_end_clean();

				$this->layout = str_replace('<!--###ACTIONPANEL###-->', $this->actionpanel, $this->layout);
				//$this->layout = str_replace('<!--###TIMER###-->', $timer, $this->layout);
			}
		
			//MENU
			if (file_exists($this->menupath)) {				
				ob_start();
				include_once $this->menupath;
				$this->menu = ob_get_contents();
				ob_end_clean();

				$this->layout = str_replace('<!--###MENU###-->', $this->menu, $this->layout);
			}
			
			//MSG			
			if ($msg){
				
				if ($sessionmessage)
					$msg = $sessionmessage;
				
				if (is_array($msg)){
					$arr = $msg;
					$this->msg = "";
					foreach ( $arr as $message ){
						$this->msg .= ' + '. $message . "<br>";
							
					}
				} else {
					$this->msg = $msg;
				}
				
				$this->layout = str_replace('<!--###MSG###-->', $this->msg, $this->layout);					
			}
			
			
			
			//////////		
			$this->output = str_replace('<!--###CONTENT###-->', $this->content, $this->layout);
			//Fertiger content steht in $this->output!
			
			//CSS Things
			
			$css_s[] = $this->themecss;
			$css_s[] = $this->maincss;
			
			if (isset($css_s)){
				if (is_array($css_s)){
					#krsort($css_s);
					ob_start();
					
					foreach ($css_s as $css)
						echo '<link rel="stylesheet" type="text/css" href="'.$css.'" />'."\n";
					
					$this->css = ob_get_contents();
					ob_end_clean();
					
					$this->output = str_replace('<!--###CSS###-->', $this->css, $this->output);
				}
				
			}
			//LANGUAGE THINGS
			$lang = get_lang();
			$this->output = str_replace('<!--###LANG###-->', $lang, $this->output);

			
			//JavaScript Things
			//generate lang files of not exists
			$arcanum_js = $this->js_lang . $lang . '.js';
			if (is_writable(dirname($this->js_lang_realpath))) {
				$langjsfile = $this->js_lang_realpath . $lang . '.js';					
				if ( !(file_exists($langjsfile)) || (filectime($this->configfile) >= filectime($langjsfile)) || (filectime($this->mainjs_realpath) >= filectime($langjsfile)) ) {
					logit('Re-Creating JS for lang: '.$lang);
					ob_start();
					include($this->configfile);
					include($this->mainjs_realpath);
					$jscontent = ob_get_contents();
					ob_end_clean();
					if (!file_put_contents($langjsfile, $jscontent))
						$js_fallback = TRUE;

				}
			} else {
				$js_fallback = TRUE;
			}

			if ($js_fallback == TRUE) {
				logit('Js Patch not writable, fallback to dynamically created file');
				$arcanum_js = $this->mainjs;
			}


			//JavaScript Things
			//Set jS lang file or fallback AND include HTML Haad JS
			ob_start();

			echo '<script type="text/javascript" src="'.$this->jquery.'"></script>'."\n";
			echo '<script type="text/javascript" src="'.$arcanum_js.'"></script>';
			if (isset($java_scripts)){
				if (is_array($java_scripts)){
					foreach ($java_scripts as $js)
					echo "\n".'<script type="text/javascript" src="'.$js.'"></script>';
				}
			}
			
			if (isset($java_script))
				echo "\n"
					.'<script type="text/javascript">'
					."\n".'/* <![CDATA[ */'
					."\n".trim($java_script)
					."\n".'/* ]]> */'
					."\n".'</script>';
			
			$this->js = ob_get_contents();
			ob_end_clean();
			
			$this->output = str_replace('<!--###JAVASCRIPT###-->', $this->js, $this->output);


		} else {
			//NO LAYOUT i.E. in case of AJAX answers
			$this->output = $this->content;
		}
			
		
		return TRUE;
	}

	
	
	public function display() {
		echo $this->output;

	}
	
}

?>
