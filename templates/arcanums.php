<div class="arcanums">
<?
	if (is_array($data)){		
		$inactive = array();
		
		foreach ($data as $id => $row){
			if ($row['active'] == "y"){
				$idactive = $id;
				$out[$id] = $row;
			} else {
				$inactive[$id] = $row;
			}
		}
		
		if (count($inactive) >= 1){
			foreach ($inactive as $arcanum){
				$inactives[$arcanum['created']]	= $arcanum;
			}		
			krsort($inactives);	
			foreach ($inactives as $id => $row){
				$out[$id] = $row;
			}
		}
				
		echo '<div class="show">';
		$count = count($out);
		if ($count > 1){
			$hcount = $count-1;
			
			echo '<img class="timewarp" src="' . $this->historyicon .'" alt="'.e('arcanums_history').' ('.$hcount.')" title=" '.e('arcanums_history').' ('.$hcount.')" onclick="$(\'.inactive\').toggle();">';
		}
		
		echo '
			<img class="change" onclick="change_arc_form(\''. $idactive .'\');" src="' . $this->configicon . '" alt="'.e('change').'" title="'.e('change').'">	
			<img class="gen_passwd" onclick="gen_passwd('. $idactive .','. $content['portal_id'].');" src="' . $this->genpasswdicon . '" alt="'.e('gen_passwd').'" title="'.e('gen_passwd').'" style="display: none;">

			<img class="save" onclick="save_arcanum('. $idactive .','. $content['portal_id'].');" src="' . $this->okicon . '" alt="'.e('save').'" title="'.e('save').'" style="display: none;">	
			<img class="loading" src="' . $this->reloadicon . '" alt="'.e('working').'" title="'.e('working').'" style="display: none;">
			</div>
		
		<table>
		  <tr>
		 	<th class="first">'.e('login').'</th>
		 	<th class="first">'.e('password').'</th>
		 	<th class="first">'.e('arcanums_history_changed').'</th>
		  </tr>
		';
		
		foreach ($out as $id => $row){
			$c = ($row['active'] == 'y') ? 'active' : 'inactive';
			if ($row['active'] == 'y')
				$act_id = $id;
			
			echo '
				<tr class="arcanum '.$c.'" id="arcanum_form_'.$id.'">					
					<td>
						<input id="input_login_'.$id.'" class="in login" type="text" name="name" value="'. htmlentities($row['portal_login']) .'" readonly="readonly">
					</td>
					<td>
						<input id="input_pass_'.$id.'" class="in pass" type="text" name="desc" value="'. htmlentities($row['portal_pass']) .'" readonly="readonly">
					</td>
					<td>
						'. date(e('dateopts_num'), $row['created']) .'
					</td>					
				</tr>';
		}
		
		echo '</table>';
	}

$java_script = '$("#input_pass_'.$act_id.'").focus();';
?>
<div class="clearer"></div>
</div>
<input type="button" class="button" onclick="load_arcanum('<?=$content['portal_id']?>', false);" value="<?=e('close')?>">
