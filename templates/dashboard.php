<?
$java_script = "
        $(document).ready(function() {
                $('.options_change').hide();
                change_options('#notes');
	});
";

$pw_int_days = $content['arc_pass_notify_interval']/3600/24;
?>


<div id="subnav">
	<ul>   
        	<li><a id="notes" class="choose" href="#" onclick="change_options('#notes');"><?=e('notes')?></a></li>
                <li><a id="stats"  class="choose" href="#" onclick="change_options('#stats');"><?=e('stats')?></a></li>
	</ul>
</div>


<div id="stats_change" class="options_change">
	<br /><br />
	<?
	echo e('currently_stat', array($content['count_port'], $content['count_files'], $content['count_cat']));

        if ($this->inv_mode === TRUE){
                if (isset($content['count_inv_open']) || isset($content['count_inv_closed'])){
                        $content['count_inv_closed'] = (isset($content['count_inv_closed'])) ? $content['count_inv_closed'] : 0;
                        $content['count_inv_open'] = (isset($content['count_inv_open'])) ? $content['count_inv_open'] : 0;

                        echo br().e('inv_stats', array('<b class="info">'.$content['count_inv_closed'].'</b>', '<b class="info">'.$this->inv_valid_time_days.'</b>', '<b class="info">'.$content['count_inv_open'].'</b>'), array(1,3));
                }
        }

	echo '<br><br>';
        echo e('last_pw_change', array('<b class="info">'.strftime(e('dateopts'), $content['lastupdated']).'</b>'));
        echo '<br>'.e('pw_change_remind', array($pw_int_days));

	?>
	<br /><br />
</div>


<div id="notes_change" class="options_change"> 
	<br />
	<?
       if (is_numeric($content['lastlogin']))
                echo e('lastlogin', array(strftime(e('dateopts_day'), $content['lastlogin']))).br();


        if ($content['lastupdated_to_old'] === TRUE){
                echo br().br().'<b><div class="error">';
		echo e('last_pw_change', array(strftime(e('dateopts'), $content['lastupdated'])));
		echo '<br>'.e('change_pw_suggest').'</div><br></b><br>';
        } 

	if ($this->forgot_active === TRUE){
		if (isset($content['forgot_hint_req_since_last_login'])){
			echo br().br();
			echo '<div class="error">';
			echo e('forgot_hint_req_since_last_login_info_1', array(strftime(e('dateopts'), $content['forgot_hint_req_since_last_login'])), array(1));
			echo br().e('forgot_hint_req_since_last_login_info_2');
			echo '</div>';
		}
	
		if (isset($content['use_forgot_not_active'])){
			echo '<br><br><div class="error">';
			echo e('use_forgot_not_active');
			echo '</div>';	
		}
	}

	$i = e('remember_arc', array($pw_int_days));
        if (isset($content['bad_pws'])){
                echo br().img($this->infoicon).'&nbsp;'.e('bad_pws');
                echo '<div class="look">';
                foreach ($content['bad_pws'] as $cat_name => $portals) {
                        foreach ($portals as $port_name => $pid ){
				echo '<div id="old_pw_'.$pid.'">'.img($this->eyeicon,$i,$i,'','','remember_arc(\''.$pid.'\');')."&nbsp;&nbsp;<b class='info'>".$port_name."</b> [".$cat_name."]</div>";
                        }
                }
                echo '</div>';
        }

        if (isset($content['old_pws'])){
                echo br().img($this->infoicon).'&nbsp;'.e('outdated_pws', array($pw_int_days), array(1)); 
                echo '<br><div class="look">';
                foreach ($content['old_pws'] as $cat_name => $portals) {
                        foreach ($portals as $port_name => $pid ){
                                echo '<div id="old_pw_'.$pid.'">'.img($this->eyeicon,$i,$i,'','','remember_arc(\''.$pid.'\');')."&nbsp;&nbsp;<b class='info'>".$port_name."</b> [".$cat_name."]</div>";
                        }
                }
                echo '</div>';
        }

        if (isset($content['double_pws'])){
                echo br().img($this->infoicon).'&nbsp;'.e('double_pws');
                echo '<br><div class="look">';
                foreach ($content['double_pws'] as $cat_name => $portals) {
                        foreach ($portals as $port_name => $pid ){
				echo "<b class='info'>".$port_name."</b> [".$cat_name."]<br>";
                        }
                }
                echo '</div>';
        }
	if ($content['showall'] == FALSE)
		echo br().br().'<b><a href="'.link_for('dashboard','showall').'">['.e('dashboard_showall').']</a></b>'.br();
	?>


        <br />
</div>
