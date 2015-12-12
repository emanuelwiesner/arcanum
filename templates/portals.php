<?php
if (is_array($data)){
	echo '<div class="portals">';
	foreach ($data as $id => $row){
		
		//AUTOLOGIN?		
		$auth_link = $auth_link_post = FALSE;
		$input_val = '0';
		$linkv = '';
		$link = '';
		if (strpos($row['link'], $this->autologinprepend) !== FALSE) {
			$row['link'] = preg_replace('%'.$this->autologinprepend.'%', '', $row['link']);				
				
			if (isset($content[$id])){
					
				if ( preg_grep('%^http%', array($row['link'])) ){						
					$auth_link = TRUE;
					$row['link'] = preg_replace("%[\n\r]%","",$row['link']); 
				
					$content[$id]['portal_pass'] = ($content[$id]['portal_pass'] != '') ? ':'.$content[$id]['portal_pass'] : '';
					
					$auth_link = preg_replace("%://%", '://' . htmlentities($content[$id]['portal_login']) . htmlentities($content[$id]['portal_pass']) . '@', $row['link']);
					
				} elseif ( preg_grep('%link=%', array($row['link'])) ){					

					$arr = @parse_ini_string( $row['link'] );
					if ($arr != FALSE){
						$auth_link_post = TRUE;
					
						$auth_link  = '<form action="'.$arr['link'].'" target="_blank"';
						
						if (!(isset($arr['method'])))
							$arr['method'] = 'post';
						
						$auth_link .= 'method="'.$arr['method'].'">';
						
						unset($arr['method'], $arr['link']);
						
						foreach ($arr as $key => $val) {
							
							if ($val == 'USERNAME'){
								$val = htmlentities($content[$id]['portal_login']);
							} elseif ($val == 'PASSWORD') {
								$val = htmlentities($content[$id]['portal_pass']);
							}
							
							$auth_link .= '<input type="hidden" name="'.$key.'" value="'.$val.'">';
						}
						
						$auth_link .= '<input class="autologinbutton" type="image" src="' . $this->loginicon . '" alt="'.e('login').'"></form>';	
					} else {
						$auth_link = img($this->loginicon, e('autologin_problem'), e('autologin_problem'));
						$auth_link = '';
					}
				}
			}

			if ($auth_link) {
				if ($auth_link_post){
					$input_val = '1';
					$linkv = 'style="display:none;"';
					$link = $auth_link;
				} else {
					$input_val = '0';
					$linkv = 'style="display:none;"';
					$link = '<a class="link" target="_blank" href="'.$auth_link.'">'. img($this->loginicon, e('login'), e('login')) .'</a>';
				}
			}

		} else { //NO AUTOLOGIN
				$input_val = '0';
				$linkv = '';

				if ($row['link'] != '') 
					$link = '<a class="link" target="_blank" href="'.$row['link'].'">'. img($this->loginicon, e('login'), e('login')) .'</a>';
				else
					$link = '';
		}
		//<-- AUTOLOGIN
		
		
		$common_used = ($row['common_used'] == "") ? "no" : $row['common_used'];
		echo '<div class="portal black" id="portal_form_'.$id.'">';
		
		
		echo '
						<div class="portalsholder" style="float: left;">
							<input id="input_name_'.$id.'" class="in" type="text" name="name" value="'. $row['name'] .'" readonly="readonly">
								
			';
		
							if (!(defined('HIDE_DESC')))
								echo '<input id="input_desc_'.$id.'" class="in" type="text" name="desc" value="'. $row['desc'].'" readonly="readonly">';
		echo '				
		
		
								<div class="portalsholderaction">
								<div style="float: left;">
									<img class="change changeportal" onclick="change_portal_form(\''. $id .'\');" src="' . $this->configicon . '" alt="'.e('change').'" title="'.e('change').'">	
								</div>
							
								<div style="float: left;" class="arcanum_decrypt" id="load_arcanum_'. $id .'" onclick="load_arcanum(' . $id . ');"></div>
								<div style="float: left;" class="common_used_'. $common_used .'" id="common_used_'. $id .'" onclick="load_common_used(' . $id . ');"></div>
								

						';
						
			if ($link != '')
				echo '<div style="float: left;" class="autolink">'.$link.'</div>';
								
			echo '		
								<div class="clearer"></div>
							</div>
				';
				
			//echo ($link != '') ? '<textarea id="input_link_'.$id.'" class="in arc_textarea" name="link" cols="5" rows="1" '.$linkv.' readonly="readonly">'. $row['link'].'</textarea>' : '<input type="text" id="input_link_'.$id.'" class="in" name="link" readonly="readonly" value="'. $row['link'].'" />';
			if ($link != '')
				$out_text_area = '<textarea id="input_link_'.$id.'" class="in arc_textarea" name="link" cols="5" rows="1" style="display: none;" readonly="readonly">'. $row['link'].'</textarea>';
			else
				$out_text_area = '<textarea id="input_link_'.$id.'" class="in arc_textarea" style="display: none;" name="link" readonly="readonly"></textarea>';
		
			echo $out_text_area;

			//echo '<textarea id="input_link_'.$id.'" class="in arc_textarea" name="link" cols="5" rows="5" '.$linkv.' readonly="readonly">'. $row['link'].'</textarea>';
			$autologinicon = ($auth_link) ? $this->autologiniconactive : $this->autologinicon;
			$autologinalt = ($auth_link) ? 'active' : 'inactive';
			$autologinclass = ($auth_link) ? ' loaded' : '';
			
			echo '							
							<input type="hidden" name="id" value="'. $id.'">					

							<div class="portalsholderchangeaction">	
									<img onclick="save_portal(\''. $id .'\');" class="save saveportal" src="' . $this->okicon . '" alt="'.e('save').'" title="'.e('save').'" style="display: none;">							
									<img onclick="del_portal(\''. $id .'\');" class="delete deleteportal" src="' . $this->minusicon . '" alt="'.e('delete').'" title="'.e('delete').'" style="display: none;">
							
									<input type="hidden" id="portal_loaded_autologin_'. $id .'" value="'. $input_val .'" />
									<input type="hidden" id="portal_autologin_'. $id .'" value="'. $input_val .'" />
									<img onclick="change_autologin('.$id.', \'portal\');" class="autologin'.$autologinclass.'" src="' . $autologinicon . '" alt="'.e('autologin_toggle').'" title="'.e('autologin_toggle').$autologinalt.'" style="display: none;" />					
							</div>
						</div>
						
							

							
						<div class="clearer"></div>
						<div id="arcanum_'. $id .'" class="arc"></div>	
			';
				
		echo '</div>';
	}
	
	echo '</div>';
}
?>
