<?php
if (($modulename == 'categories') && ($this->request['action'] == 'favs')){
	$modulename = 'favs';
}

if (($auth  == TRUE) || defined('MENUAUTH')) {
	$entries = array(
			10 => "dashboard", 
			20 => "categories",			
			30 => "favs",
			40 => "files",
			50 => "memo",
			60 => "invitation",
			70 => "log",
			75 => "export",
			80 => "settings"
	);
	
	if ($this->inv_mode === FALSE)
		unset($entries[60]);
	
	$extra_entry = "Logout";
	$extra_module = "login";
	$extra_action = "logout";
	//$extra_attr = 'onclick="$(\'#loadingscreen\').show();"';
	$extra_attr = '';

	if (isset($content['timer_display_content']))	
		$timer = $content['timer_display_content'];
	
} else {
	$entries = array(
		10 => 'login', 
		20 => 'register',
		30 => 'howitworks',
		40 => 'imprint'
	);
	if ($this->forgot_active === TRUE){
		$entries[25] = 'forgot';
	}
	
	$extra_module = "imprint";
	$extra_entry = e($extra_module);
	$extra_action = "";
	$extra_attr = '';
	
	
	$timer = "";	
}

ksort($entries);
	$menu = '<ul>';
	foreach ($entries as $prio => $module){
		
		if ($module == $modulename){
			$menu .= '<li class="active">';
		} else {
			$menu .= '<li>';
		}
		
		if (isset($auth)) {
			/*
			$menu .= '<a onclick="
			$.get(\'' . link_for($module, 'show&xload=300') . '\', 
				function(data){
					$(\'#content\').html(data);
				});
				"
				href="#">'.e($module).'
				</a></li>
			';
			 */
			$menu .= '<a href="' . link_for($module) .'">'.e($module).'</a></li>';
		} else {
			$menu .= '<a href="' . link_for($module) .'">'.e($module).'</a></li>';
		}
	}
	
	if ($extra_module == $modulename){
		$extra = ' actvive';
	} else {
		$extra = '';
	}
	
	$menu .= '</ul>';

if (defined('VIEW_FALLBACK')){
	switch (VIEW_FALLBACK){
		case 'JAILED':
			$menu = e('jailed_menu');
			break;

		case 'DB_ERROR':
			$menu = ('db_error');
			break;

                case 'MAINTENANCE':
                        $menu = e('maintenance_mode');
                        break;

		default:
			$menu = e('unknown_error');
	}
}
echo $menu;
?>
