<?php
function __autoload($class)
{
	global $config;
	if ((in_array($class, $config['registered_modules'])) && (preg_grep('/^[a-zA-Z]+$/', array($class)))){
		$moduleFile = $config['libpath'] .  $class . ".php";
	
		if (file_exists($moduleFile)) {
	
			require_once($config['libpath'] .  $class . ".php");
			
			if (!(class_exists($class)))
				throw new arcException("Autoloader could not find class: '$class' in $moduleFile");
	
		} else {
			throw new arcException("Autoloader could not find modulefile: $moduleFile");
		}

	} else { //not registered
		$text = ($_SERVER['REDIRECT_STATUS'] == '403') ? e('403_forbidden') : e('404_not_found');
		$arc = new arcanum();
		$arc->view_fallback ('',$text, '');
	}
}

function check_environment () {
	global $config;	

	//check mcrypt
	if (!(function_exists('mcrypt_encrypt')))
		throw new arcException('PHPs mcrypt library not available!');
	
	//check ciphers
	foreach ($config['used_mcrypt_ciphers'] as $cipher => $mode){
		if (!(mcrypt_module_self_test($cipher)))
			throw new arcException('Trying to use cipher ['.$cipher.'], but self-test failed!');
	}
	
	//check paths
	if (!(is_dir($config['tmppath']))){
		@mkdir($config['tmppath']);
		chmod($config['tmppath'], 0700);
	}

        if (!(is_writable($config['tmppath'])))
		throw new arcException("tmppath ".$config['tmppath']." is not writable!");

	if (!(is_dir($config['cache_path']))){
                @mkdir($config['cache_path']);
				chmod($config['cache_path'], 0700);
	}

        if (!(is_writable($config['cache_path'])))
                throw new arcException("cache_path ".$config['cache_path']." is not writable!");

	$virusscanner = explode(' ',$config['virusscanner']); 
        if (!(is_executable($virusscanner[0])) && ($config['enablevirusscan'] === TRUE))
                throw new arcException ("Filescanner [".$config['virusscanner']."] not executable");

	if (!(is_dir($config['js_lang_realpath']))){
                @mkdir($config['js_lang_realpath']);
		//dont throw any arcException, arcanum may work anyway
        }

}
function check_htaccess () {
	global $config;

	$htaccess = $this_dir.'.htaccess';
	$htaccessc = @file($htaccess);
	$confrel = trim(str_replace('#Arcanum ','',$htaccessc[0]));

	if ($confrel != $config['relpath']) {
		if (!(is_file($config['htaccessdummy'])))
			throw new arcException (".htacess not correctly configured and dummy file (".$config['htaccessdummy'].") not found");		

		$newhtaccess = file_get_contents($config['htaccessdummy']);
		$newhtaccess = str_replace('<!--RELPATH-->', $config['relpath'], $newhtaccess);
		$newhtaccess = str_replace('<!--HOSTESCAPED-->', str_replace('.', '\.',$config['host']), $newhtaccess);
		$newhtaccess = str_replace('<!--HOST-->', $_SERVER['HTTP_HOST'], $newhtaccess);
		$newhtaccess = str_replace('<!--PORT-->', ($config['https']==TRUE)?443:80, $newhtaccess);
		$newhtaccess = str_replace('<!--PROTOCOL-->', ($config['https']==TRUE)?'https':'http', $newhtaccess);
		file_put_contents($htaccess, $newhtaccess);
	}
}


function test_arcanum_db ($module) {
	global $config;

	$db = $config['database_dsn']; 
        if ($db['phptype'] == 'mysqli'){

        	if (!(function_exists('mysqli_connect')))
                	throw new arcException('PHPs mysqli library not available!');
                 
		$host = (isset($db['hostspec'])) ? $db['hostspec'] : ini_get("mysqli.default_host");
	        $username = (isset($db['username'])) ? $db['username'] :ini_get("mysqli.default_user");
		$passwd = (isset($db['password'])) ? $db['password'] :ini_get("mysqli.default_pw");
		$port = (isset($db['port'])) ? $db['port'] :ini_get("mysqli.default_port");
                $socket = (isset($db['socket'])) ? $db['socket'] :ini_get("mysqli.default_socket");

		$ret = @mysqli_connect($host, $username, $passwd, $db['database'], $port, $socket);

		if (($ret == TRUE) && ($module == 'login')) { //assuming first run if login is requested
			if (!(file_exists($database_models_location.DS.$db['phptype'].DS.$db['database'].'.ini'))) {
				if (!(file_exists($config['database_models_location'].DS.$db['phptype'].DS.$config['mdb2inifile'])))
					throw new arcException('The database model file for arcanum ['.$config['database_models_location'].DS.$db['phptype'].DS.$config['mdb2inifile'].'] was not found, please reload the whole arcanum package');
				copy($config['database_models_location'].DS.$db['phptype'].DS.$config['mdb2inifile'], $config['database_models_location'].DS.$db['phptype'].DS.$db['database'].'.ini');
			}

			//check database for needed tables - in case of emtpy DB: create all tables
			$conn = $ret;
			$result = $conn->query('SHOW TABLES FROM `'.$db['database'].'`');
			if($result !== false) {
				if($result->num_rows === 0) {
					if (!(file_exists($config['database_sql_file'])))
						throw new arcException('Database SQL-File not found, cannot create schema');

					$all_lines = file($config['database_sql_file'], FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
					$query = '';
					foreach($all_lines as $queryl) {
						if(substr($queryl, 0, 2) == "--")
							continue;

						$query .= $queryl;
					}
					$resc = $conn->multi_query($query);
					if($resc != TRUE)
						throw new arcException('Problems while creating TABLES in '.$db['database'].' MYSQL-Error: '.$conn->error);
					
					throw new arcException('TABLES in '.$db['database'].' SUCCESSFULLY CREATED');
				} else {
					$dbout = array();
					while ($row = $result->fetch_array(MYSQLI_NUM)) {
						$dbout[] = $row[0];
					}
					foreach ($config['database_tables_needed'] as $table) {
						if (!(in_array($table, $dbout))){
							throw new arcException('table ['.$table.'] in '.$db['database'].' is missing!');
						}
					}
				}
			}
			$conn->close();
		}
			return($ret);	
	}
}

class arcException extends Exception
{
	public function get_arc_message()
	{
		foreach ($_POST as $key => $val){			
			$postparams = '';
			if (preg_grep('%pass%', array($key))){
				$postparams .= '['.$key.' => REDACTED],';
			} else {			
				$postparams .= '['.$key.' => '.$val.'],';
			}
		}
		
		foreach ($_GET as $key => $val){			
			$getparams = '';
			if (preg_grep('%pass%', array($key))){
				$getparams .= '['.$key.' => REDACTED],';
			} else {			
				$getparams .= '['.$key.' => '.$val.'],';
			}
		}		
		
		//error message
		$errorMsg = "\n".'Exception thrown in Line ['.$this->getLine().'] in ['.$this->getFile()."]\n\n<b>". $this->getMessage()."</b>";
		if (isset($postparams))	
			$errorMsg .= "\n".'POST-PARAMS: '.$postparams;
		if (isset($getparams))
			$errorMsg .= "\n".'GET-PARAMS: '.$getparams;
		
		return $errorMsg;
	}

	public function get_arc_code () {
		return ($this->getCode() == 0) ? 500 : $this->getCode();
	}
}
/*
set_error_handler('exception_error_handler');
function exception_error_handler( $errno, $errstr, $errfile, $errline ) {
	    
	switch($errno) 
	{ 
        	//case E_WARNING: 
	        case E_RECOVERABLE_ERROR: 
	        //case E_USER_WARNING: 
		case E_ERROR:
		case E_USER_ERROR:
	        	throw new arcException ($errstr, 500); 
		break; 

	}
}
*/
function debug($what, $arr = FALSE){
	echo "<br>";

	if (is_string($what))
		echo "<b>".$what."</b>";
	
	if ($arr != FALSE) {
		echo "<pre>";
        	print_r($what);
        	echo "</pre>";
	}
}

function br () { return "<br>"; }
function nl () { return "\n"; }

function redirect ($module, $action = "", $msg = "", $code = "301" , $old = FALSE) {

		$link = link_for($module, $action, $old);

		if ($msg != "")
			$link .= '&msg=' . serialize($msg);
		
		@ob_end_clean();
		header("Location: $link", TRUE, $code);
		exit;
}

function link_for ($module, $action = "", $old = FALSE, $addon = '') {

	global $config;
	($config['https'] === TRUE) ? $protocol = 'https' : $protocol = 'http';


	$host  = $config['host'];
	//$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$url = $host.$config['relpath'].'index.php';

	$extra = '?module='. $module;
	
	if ($action != "")
		$extra .= '&amp;action=' . $action;

	
	$act = ( $action == "" ) ? '' : '/'.$action;
	$link = "$protocol://$url/$extra";
	//$link = $protocol .'://'. $host . str_replace('index.php', $module, $_SERVER['PHP_SELF']) .$act;
	$link = $protocol .'://'. $host . $config['relpath'] . $module . $act;
	
	if ($old)
		$link = "$protocol://$url$extra";

	if ($addon != '')
		$link .= $addon;
	
	return ($link);
}

function img ($src, $alt = "", $title = "", $size = "", $href = "", $onlick =""){
	
	$img = '';
	if ($href != ""){
		$img .= '<a href="'.$href.'">';
	}
	$img .= '<img src="'. $src .'"';
	
	$img .= ' alt="'.$alt.'" ';

	
	if ($title != ""){
		$img .= ' title=" '.$title.' " ';
	}
	
	if ($size != ""){
		$img .= ' '.$size;
	}
	
	if ($onlick != ""){
		$img .= 'onclick="'.$onlick.'"';
	}

	$img .= '/>';
	
	if ($href != ""){
		$img .= '</a>';
	}
	
	return $img;
}

function captchavalue ($count = 0){
	global $config;
	$takethis = $config['captchavalue'];

	$makeme = ($count != 0) ? $count . $takethis : $takethis;

	return substr(md5($makeme), 0 , 5);	
}

function mailaddress_hash ($mailaddress){
	return (substr(md5(crc32($mailaddress)), 0, 5));
}

function logit ($text){
	global $config;	
	$logfile = $config['logfile'];
	
	if (!(file_exists($logfile)))
		touch($logfile);

	if (is_array($text))
		$text = print_r($text, TRUE);

	
	$data = "\n[" . strftime($config['dateopts_log']). " (" . UIP  . ")] " . strip_tags($text);	
	file_put_contents($logfile, $data, FILE_APPEND);
}

class measure_time {

	private $start_time;

	function __construct () {
		$this->start_time = (float) array_sum(explode(' ',microtime()));	
	}

	function __destruct () {
		global $config;

	        $end = (float) array_sum(explode(' ',microtime()));
		$exec_time = $end - $this->start_time;
	        $action = (isset($_GET['action'])) ? $_GET['action'] : 'show';                          

	        $stat_log = $_GET['module'].'/'.$action.'#'.memory_get_peak_usage(TRUE).'#'.$exec_time."\n";
	        file_put_contents($config['log_stats_file'], $stat_log, FILE_APPEND);
	}
}


class Spokesman {
	
	private static $instance;
	private $all;
	private $count = 0;
	
	public function __construct() {
    }
	
	public static function cook_once(){  
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }
	
	
	public function parse_arc_phrases ($fallback = FALSE){
		global $config;

		if (!(isset($this->lang)))
			$this->lang = get_lang();
		
		if ($fallback)
			$lang = $config['arc_lang_fallback'];
		else
			$lang = $this->lang;
		
		if (!(isset($this->phrases[$lang]))){
		
			$tongue = (in_array($lang, $config['arc_langs'])) ? $lang : $config['arc_lang_fallback'];
			
			$cachefile = $config['cache_path'].$config['cache_file_ext'].$tongue;
			$origfile = $config['arc_lang_dir'].$tongue.$config['arc_lang_ext'];
			
			if ( is_file($cachefile) && (is_writable($cachefile) && (filectime($origfile) < filectime($cachefile)))){
				$this->phrases[$lang] = unserialize(file_get_contents($cachefile));
			} else {
				if (!($this->phrases[$lang] = parse_ini_file($origfile))) {
					logit("Error while parsing ".$origfile);
				} else {
					file_put_contents($cachefile, serialize($this->phrases[$lang]));
					chmod($cachefile, 0600);
				}
			}
		}
		
		return $this->phrases[$lang];
	}
}
function get_lang () {
	global $config;

	if (defined('LANG')){
		$lang = LANG;
	} elseif (isset($_COOKIE[$config['lang_cookiename']])){
		$lang = $_COOKIE[$config['lang_cookiename']];
	} elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	} else {
		$lang = $config['arc_lang_fallback'];
	}
	return ($lang);
}

function e ($what, $rep = FALSE, $make_fat = array()){

	$spokesman = Spokesman::cook_once();	
	$all = $spokesman->parse_arc_phrases();	
	
	if (isset($all[$what])){
		$phrase = $all[$what];
	} else {
		$fallback = $spokesman->parse_arc_phrases($fallback = TRUE);
		if (isset($fallback[$what])){
			$phrase = $fallback[$what];
		} else {
			return('¿'.$what.'?');
		}
	}
	
	$rep_count = substr_count($phrase, '<--#-->');
	if ( ($rep_count > 0) && is_array($rep) ){
		if (count($rep) == $rep_count){
			$c = 1;
			foreach ($rep as $r){
				$r = (in_array($c,$make_fat)) ? '<b>'.$r.'</b>' : $r;				
				$phrase = preg_replace('%<--#-->%', $r, $phrase, 1);
				$c++;
			}
		} else {
			return('!'.$what.'¡');
		}
	}
	
	return $phrase;
	//return 'TRANSLATED -> ['.$phrase.'] <- TRANSLATED';
}
?>
