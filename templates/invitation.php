<?php if (($this->inv_mode === TRUE)): ?>
<?php 
$open_module = isset($data)?"open_inv":"inv_mail";
$java_script = "
	$(document).ready(function() {
		change_options('#". $open_module . "');	
		//ji
	});
";
$sort = $data;
unset($data);
if (is_array($sort)){
	foreach ($sort as $key => $inv){
		$data[$inv['time']] = $inv;
		$data[$inv['time']]['id'] = $key;
	}
	krsort($data);
}

@$count = count($content['open']) + count($content['closed']);

$msg = e('inv_valid_time',array($this->inv_valid_time_days));
?>

	<div id="subnav">
		<div style="float: left;">
				<ul>
					<li><a id="open_inv" class="choose" href="#" onclick="change_options('#open_inv');"><?=e('invs')?></a></li>
					<?php if ($content['left'] > 0): ?>
						<li><a id="inv_mail" class="choose" href="#" onclick="change_options('#inv_mail');"><?=e('new_inv')?></a></li>
					<?php endif; ?>
				</ul>		
			</div>	
			<div class="clearer"></div>
	</div>

	<!-- INV STATS -->
	<div id="open_inv_change" class="options_change" style="display: none;">		
		<?php
			
			echo ($content['left'] > 0) ? e('invs_left', array('<b class="info">'.$content['left'].'</b>', '<b class="info">'.$this->max_invs_per_month.'</b>')) : e('no_invs_left');

			
			if (isset($data)){
				if (!(isset($content['closed'])))
					$content['closed'] = array();

				if (!(isset($content['deleted'])))
					$content['deleted'] = array();
				
				echo '<table>';
				echo '<tr><td><b>'.e('reciepent').'</b></td><td><b>'.e('inv_date').'</b></td><td><b>'.e('status').'</b></td></tr>';	
							
				foreach ($data as $time => $inv){					
					echo "<tr><td>".$inv['receipient'] ."</td>";
					echo "<td>". strftime(e('dateopts'),  $time) ."</td>";
					echo "<td>";
					
					if (in_array($inv['id'], $content['closed'])){
						echo img($this->okicon,e('inv_accepted'),e('inv_accepted'));
					} elseif (in_array($inv['id'], $content['deleted'])) {
						echo img($this->minusicon,e('inv_deleted'),e('inv_deleted'));
					} else {
						echo img($this->mailicon,e('inv_open'),e('inv_open'));
					}

					echo "</td></tr>";
				}

				echo '</table>';

			} else {
				echo br().br().br().img($this->infoicon).e('no_invs_yet');
			}
	
		?>	

	</div>

	<!-- SEND INV -->
	<div id="inv_mail_change" class="options_change" style="display: none;">
		<form name="inv_mail_form" method="post" action="<?=link_for('invitation', 'add')?>" target="_parent">
			<table>
				<tr><td class="info"><label for="category"><?=e('mail_address')?></label></td><td><input type="text" class="text" name="receipient" value="" style="direction:ltr" /></td></tr>
				<tr><td class="info"><label for="category"><?=e('subject')?></label></td><td><?=e('auto_inv_subj')?></td></tr>
				<tr><td></td><td><?=e('auto_inv_pre_text_sender')?></td></tr>
				<tr><td class="info"><label for="category"><?=e('message')?></label></td><td><textarea class="text" name="message" value="" style="direction:ltr" /></textarea></td></tr>
				<tr><td></td><td><input type="hidden" name="crc_add_inv" value="<?=$this->crc?>" /><input id="login-button" name="inv_submit" class="button submit-button" value="<?=e('send_inv')?>" type="submit" /></td></tr>
			</table>
			<br>
			<?=e('inv_info',array('<br>'))?>
		</form>	
	</div>
<?php else: ?>
	<?=e('inv_deactivated')?>
<?php endif; ?>
	
