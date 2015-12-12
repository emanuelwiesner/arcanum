<?php
if (!(headers_sent()))
        header('X-Powered-By: arcanum');

require_once('config.php');

$request = array_merge($_GET, $_POST);
if (!(isset($request['module']))) {
        if ($config['checkhtaccess'] == TRUE)
        	check_htaccess(); //there are a lot of useful functions like: check_environment() and test_arcanum_db()

	redirect('login');
} else {
	$module = strtolower(trim($request['module']));
}

try {
	if ($config['log_stats'] === TRUE)
		$t = new measure_time();

	check_environment();
	
	$instance = new $module();		
	$is_auth = $instance->authenticate();	
	
	if ($is_auth === TRUE) { //Check if user can be authenticated or is authenticated
		$instance->bootstrap($request);
	
		isset($request['action']) ? $action = strtolower(trim($request['action'])) : $action = 'show';
				
		$instance->execute($action);		
		$instance->dispatch();		
		$instance->cooldown();

	} else {
		$instance = new view();
		$instance->not_authenticated($is_auth, $request);
		$instance->display();
	}

} catch (arcException $e) {
	$err_msg = $e->get_arc_message() ."\nCODE: " .$e->get_arc_code() . "\nTRACE:\n". $e->getTraceAsString();
	logit($err_msg);
	
	if (is_object($instance)) {
		$instance->user_log(e('exception_'.$e->get_arc_code()));
		$instance->kill();
	} else {
		$instance = new view(TRUE);
        	$instance->set('fallback', $err_msg);
        	$instance->display();
		die();
        }

	if ($config['report_exception_mail'] != '')	
		mail($config['report_exception_mail'], '[ARCANUM: ERROR]', $err_msg);
	
	redirect('login', 'logout', e('exception_'.$e->get_arc_code()), 301, TRUE);
}
?>
