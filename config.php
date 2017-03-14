<?php
ini_set('expose_php', 0);
ini_set('display_startup_errors', 0);
ini_set('display_errors', 0); //CHANGE THIS if you got blank screen!
ini_set('allow_url_include', 0);
ini_set('allow_url_fopen', 0);
ini_set('log_errors',TRUE);
ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
ini_set('error_log','logging/php_error.log');
$this_dir = dirname(__FILE__);

define('DS', DIRECTORY_SEPARATOR);
define('TIME', time());
define('UIP', $_SERVER['REMOTE_ADDR']);
define('UAGENT', $_SERVER['HTTP_USER_AGENT']);

$this_rel = (isset($this_rel)) ? $this_rel : str_replace('index.php', '', $_SERVER['PHP_SELF']);
$icon_path = $this_rel.'dl/img/icons/';
$config = array(
/*
 => Start to edit your own settings here!
*/

		'https_warning' => TRUE,
		'checkhtaccess' => TRUE, //auto-create .htaccess of incorrect or missing

                'geoiptool_api' => 'https://geoiptool.com/en/?ip=', //use what you want
		'virusscanner' => '/usr/bin/clamdscan --infected --stream --no-summary', //put in viruscanner with xargs, filename will be appendes

		'inv_mode' => FALSE, //by default, arcanum works with invitations - only registered users can invite others. Disable for first use.

		'database_ssl_enable' => FALSE, //could be safely disabled for local and/or socket connections
		'database_dsn' => array( //mysqli
                        'phptype' => 'mysqli',
                        'database' => 'enter_your_database_name_here',
                        'username' => 'enter_your_database_username_here',
                        'password' => 'enter_your_database_password_here',
                        'charset'  => 'utf8', //do not change!
			'hostspec' => 'localhost',
                        //'socket'   => '/var/run/mysqld/mysqld.sock', //I prefer sockets, you can commet this and use the following 'hostpec'
			//'ca' => $this_dir.DS.'sqlca.pem',
                ),

		'imports_only_on_empty_db' => TRUE, //maybe buggy?
/*
 => The following settings could be changed, but there is no need to do that
*/

		'database_debug_lv' => 0, //CHANGE HERE TO THE VALUE OF 5 IN CASE OF DB ERRORS
		'database_models_location' => $this_dir.DS.'model', //no need to change.
                'database_sql_file' => $this_dir.DS.'model'.DS.'arcanum.sql', //dont change
                'database_tables_needed' => array('arcanums','categories','files','forgot','invitations','jail','log','portals','settings','users'), //only change in case of additional tables

		'mdb2inifile' => 'arcanum.ini', //do not change and never delete this file!

                'cache_path' => $this_dir . '/tmp/', //for language things only - could be in webroot, in example: $this_rel.'somedirectory',
                'cache_file_ext' => 'arc_tempCACHED_', //change as you wish
                'version' => '0.9.9-M', //have fun
                'configfile' => __FILE__, //do not change this

                'maintenance_mode' => FALSE, //this makes arcanum to show a simple maintenance site
                'maintenance_ip' => 'ip6_or4', //single ip4 or ip6 adress, which could use arcanum when 'maintenance_mode' is active
                'maintenance_debug' => FALSE, //enable to show debug options, when in maintenance
                'report_exception_mail' => 'root', //enter a mail adress to which exceptions should be mailed, otherwise left blank

                //Languages
                'arc_lang_dir' => $this_dir.DS.'dl'.DS.'lang'.DS,
                'arc_lang_fallback' => 'en',
                'arc_lang_ext' => '.ini',
                'arc_' => '',
                'lang_cookiename' => 'arc_lang',
		'arc_langs' => array('english' => 'en','deutsch' => 'de'),

		'min_patterlock_lenght' => 3,
		'max_input_length_no_auth' => 200, //A non authenticated user is only allowed to post that amount of chars in one variable
                'ip_sec_check' => TRUE, //rely on IP in sec check
                'ipv6switchgraceactive' => TRUE, //allow switch to an other IP protocol - experimental.
                'enablevirusscan' => TRUE, //check uploaded files for viruses before store in DB
                'file_integrity_check' => FALSE, //check main file (lib/arcanum.php) for changes - for logged in people.
		'https' => (@$_SERVER['HTTPS'] == "on") ? TRUE : FALSE,

		//could be changed
                'session_key' => 'arcanum',
                'session_hashkey' => 'arcanum_sha',
                'session_integrity_checksum' => 'arcanum_integrity',

		//path things
		'htaccessdummy' => $this_dir.DS.'model'.DS.'htaccess.txt',
                'pear_path' => $this_dir.DS.'lib/PEAR',
                'basepath' => $this_dir.DS,
                'tmppath' => $this_dir.DS."tmp".DS,
                'tmp_clean_time' => 32400,
                'logfile' => $this_dir.DS."logging".DS."arcanum.log", //general log
		'dateopts_log' => '%A, %d. %m. %R',
		'log_stats' => FALSE, //perfomance log
                'log_stats_file' => $this_dir.DS."logging".DS."stats.log",
                'libpath' => $this_dir.DS."lib".DS,
                'templatepath' => $this_dir.DS."templates".DS,
                'layoutpath' => $this_dir.DS."templates".DS."layout.php",
                'menupath' => $this_dir.DS."templates".DS."menu.php",
                'actionpanelpath' => $this_dir.DS."templates".DS."actionpanel.php",
                'msgpath' => $this_dir.DS."templates".DS."msg.php",
                'deleteconfirmpath' => $this_dir.DS."templates".DS."deleteconfirm.php",
                'jspath' => $this_dir.DS."dl".DS."js".DS,
                'csspath' => $this_dir.DS."dl".DS."css".DS,
		'relpath' => $this_rel,
		'host' => $_SERVER['HTTP_HOST'],
		'jquery' => $this_rel.'dl/js/jquery-3.1.1.min.js',
	
		//graphics
		'maincss' => $this_rel.'dl/css/styles.css',
		'themecss' => $this_rel.'dl/css/content.css',
		'mainjs' => $this_rel.'dl/js/main.js.php',
		'mainjs_realpath' => $this_dir.DS.'dl'.DS.'js'.DS.'main.js.php',
		'js_lang' => $this_rel.'dl/js/lang/',
		'js_lang_realpath' => $this_dir.DS.'dl'.DS.'js'.DS.'lang'.DS,
		'favicon' => $this_rel.'dl/img/favicon.png', //should be PNG, or change in templates/layout.php
		'loading' => $this_rel.'dl/img/ajax-loader.gif',
		'plusicon' => $icon_path . 'plus_12x12.png',
		'favyesicon' => $icon_path.'heart_fill_12x11.png',
		'favnoicon' => $icon_path.'heart_stroke_12x11.png',
		'configicon' => $icon_path. 'wrench_12x12.png',
		'decrypticon' => $icon_path.'unlock_stroke_12x16.png',
		'okicon' => $icon_path.'check_12x10.png',
		'historyicon' => $icon_path.'clock_12x12.png',
		'downloadicon' => $icon_path.'cloud_download_16x16.png',
		'loadingicon' => $this_rel.'dl/img/ajax-loader_middle.gif',
		'reloadicon' => $this_rel.'dl/img/ajax-loader_middle.gif',
		'reloadcaptchaicon' => $icon_path.'loop_16x16.png',
		'infoicon' => $icon_path.'arrow_right_12x12.png',
		'downicon' => $icon_path.'arrow_down_16x16.png',		
		'nexticon' => $icon_path.'arrow_right_16x16.png',
		'mailicon' => $icon_path.'mail_12x9.png',
		'minusicon' => $icon_path . 'x_11x11.png',
		'loupeicon' => $icon_path . 'magnifying_glass_16x16.png',
		'searchicon' => $icon_path . 'read_more_16x16.png',
		'loginicon' => $icon_path . 'curved_arrow_16x12.png',
		'eyeicon' => $icon_path . 'eye_12x9.png',
		'helpicon' => $icon_path . 'question_mark_6x12.png',
		'printicon' => $icon_path . 'document_alt_stroke_24x32.png',
		'genpasswdicon' => $icon_path . 'share_16x16.png',
		'autologinicon' => $icon_path . 'comment_stroke_16x14.png',
		'autologiniconactive' => $icon_path . 'comment_fill_16x14.png',
                'captchabg' => $this_dir.DS."dl".DS."img".DS.'captcha'.DS.'background1.png',
                'captchattf' => $this_dir.DS."dl".DS."img".DS.'captcha'.DS.'Neverwinter.ttf',
                'captchavalue' => $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'],
	
		//crypting values
		//https://paragonie.com/white-paper/2015-secure-php-data-encryption
		//https://www.warpconduit.net/2013/04/14/highly-secure-data-encryption-decryption-made-easy-with-php-mcrypt-rijndael-256-and-cbc/
		//holy shit, do not change in production !!!!!!!!!!
		'arcanum_cryptv' => 1,

		'session_encrypt_env_vars' => array('TMPDIR','PHPRC','PATH','HTTP_ACCEPT_ENCODING', 'HTTP_ACCEPT', 'HTTP_ACCEPT_ENCODING', 'SERVER_SIGNATURE', 'HTTP_ACCEPT_LANGUAGE','SERVER_SOFTWARE','SERVER_NAME', 'HTTP_USER_AGENT', 'DOCUMENT_ROOT', 'GATEWAY_INTERFACE', 'SERVER_PROTOCOL', 'ORIG_SCRIPT_FILENAME', 'SERVER_ADMIN'),
		'autologinprepend' => '<!--ARCAUTOLOGIN-->',
		'crc' => md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']),
		'used_mcrypt_ciphers' => array(MCRYPT_RIJNDAEL_256 => MCRYPT_MODE_OFB, MCRYPT_TRIPLEDES => MCRYPT_MODE_CBC),
		'used_mcrypt_ciphers' => array(MCRYPT_RIJNDAEL_192 => MCRYPT_MODE_OFB, MCRYPT_TRIPLEDES => MCRYPT_MODE_CBC),
		'used_hash_aglos' => array('sha512', 'md5', 'whirlpool'),
		//holy shit, do not change in production !!!!!!!!!!
		
		// misc	
		'max_data_storage' => 15*1024*1024,
		'storage_upper_limit' => 50*1024*1024,
		'max_file_size' => 10*1024*1024,
		'default_colour' => '#C0FFEE',
		'regex_colour' => '/^#[a-fA-F0-9]{6}$/',
		'jail_max_tries' => 10,
		'jail_time' => 600,
		'jail_whitelist' => array($_SERVER['SERVER_ADDR'], '127.0.0.1'),
		'recommend_password_change_interval' => 90*24*60*60,
		'min_arc_pass_notify_interval' => -1,
		'max_arc_pass_notify_interval' => 360,
		'default_session_lifetime' => 3*60*60,
		'max_session_lifetime' => 12*60*60,
		'min_session_lifetime' => 1*60,
		'systemlogger' => '[SYSTEM] ',
		'logstoretime_days' => 21,
		'logstore_max' => 180,
		'log_max_show' => 30,
		
		'defaultstartmodule' => 'dashboard',

		'cookiename' => 'arcanum',
		'session_regenerate_frequency' => 8,
		'timer_refresh_rate' => 120000,
		
		'max_load' => 5,
		'min_mem' => 100,
	
		'forgot_active' => TRUE,
		'forgot_active_register' => FALSE,
		'forgot_preg' => '/^[0-9A-Za-zöäüß\s\?!]+$/',
		
		'search_active' => TRUE, //live search
		'min_search_chars' => 2, //chars for it

		'inv_abuse_mailaddr' => $_SERVER['SERVER_ADMIN'],
		'storage_per_inv' => 5*1024*1024, //additional per each inv
		'max_invs_per_month' => 15,
		'invs_time_equal_param' => '%Y%m', //per month
		'inv_valid_time_days' => 14,
		
);
$config['inv_mailh'] = 'MIME-Version: 1.0' . "\r\n";
$config['inv_mailh'] .= 'Content-type: text/plain; charset=UTF-8' . "\r\n";
$config['inv_mailh'] .= 'From: Arcanum <'.$config['inv_abuse_mailaddr'].'>' . "\r\n";
$config['inv_mailh'] .= 'Reply-To: '.$config['inv_abuse_mailaddr'] . "\r\n";
$config['inv_mailh'] .= 'X-Mailer: Arcanum v ['.$config['version'].'] powered by PHP/' . phpversion() . "\r\n";

$config['start_module'] = 'dashboard'; 
$config['registered_modules'] = array(
	'arcanum',
	'view',
	'login',
	'dashboard',
	'categories',
	'files',
	'portals',
	'timer',
	'settings',
	'register',
	'imprint',
	'favs',
	'export',
	'arcanums',
	'howitworks',
	'log',
	'getcaptcha',
	'search',
	'invitation',
	'forgot',
	'lang',
	'memo',
	'ajaxget',
	'status',
	'showip'
);

$config['registered_modules_no_auth'] = array(
	'view',
	'imprint',
	'howitworks',
	'getcaptcha',
	'register',
	'forgot',
	'lang',
	'login',
	'status',
	'showip'
);

$config['registered_start_modules'] = array(
	'dashboard',
	'categories',
	'favs',
	'files',	
	'memo',
	'log',
);
if ($config['inv_mode'] == TRUE)
	$config['registered_start_modules'][] = 'invitation';

$config ['non_volatile'] = array(
	'login' => array('tryit'),
	'register' => array('doit'),
	'forgot' => array('yrly')
)
;
$protocol = ($https == TRUE) ? 'https' : 'http';
$config['statuslink'] = $protocol . '://' . $_SERVER['HTTP_HOST'] . $this_rel . '/status/show';

setlocale (LC_ALL, array('en_EN.utf8', 'en_EN', 'english', 'english'));
ini_set('include_path', $config['pear_path']);
require_once('lib/main_funcs.php');
?>
