<div id="subnav">
	<div>
		<ul>
			<?php if ($content['showall'] == FALSE): ?>
			<li><a id="settings" class="choose" href="<?=link_for('log','showall')?>"><?=e('show_all_now', array('<b>'.$content['logcount'].'</b>'))?></a></li>	
			<?php else: ?>
			<li><a id="settings" class="choose" href="<?=link_for('log','show')?>"><?=e('show_part_now', array('<b>'.$this->log_max_show.'</b>'))?></a></li>	
			<?php endif; ?>
			<li><a id="settings" onclick="return confirm('<?=e('log_really_delete')?>')" href="<?=link_for('log','delete')?>" class="choose"><?=e('delete_logs')?></a></li>
		</ul>
	</div>
</div>

<?php 
foreach ($data as $loglines){
	$logs[$loglines['time']] = $loglines;
}

krsort($logs);

echo '<div class="info" id="log_info">';
echo e('log_info', array($this->logstoretime_days));
echo '</div>';

echo '<table id="logs">';
echo '<tr><th class="first">'.e('time').'</th><th class="first">'.e('ip').'</th><th class="first">'.e('log_e').'</th>';

$count = 0;
$lastip = '';
foreach ($logs as $time => $log){
	
	if ($content['showall'] == FALSE){
		if ($count >= $this->log_max_show)
			continue;
	}
	
	if ($lastip == $log['ip']){
		$log['ip'] = '';
	} else {
		$lastip = $log['ip'];
	}
	
	$logip = ($log['ip'] != '') ? '<a href="'.$this->geoiptool_api.$log['ip'].'" target="_blank">'.img($this->eyeicon, $log['ip'], $log['ip']).'</a>' : ''; 

	$inf = (preg_grep($this->systemlogger, array($log['log']))) ? ' class="error"' : '';
		
	echo '<tr'.$inf.'><td style="width: 112px;">' . date(e('dateopts_num'), $time) . '</td><td>'.$logip.'</td><td>' . substr($log['log'], 0, 2000) . '</td></tr>';
	$count++;
}

echo '</table>';
?>
