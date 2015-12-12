<?php
$css_s[] = $this->relpath . 'dl/css/farbtastic.css';
$css_s[] = $this->relpath . 'dl/css/jquery_tools.css';
$css_s[] = $this->relpath . 'dl/css/patternlock.css';

$java_scripts[] = $this->relpath . 'dl/js/farbtastic.js';
$java_scripts[] = $this->relpath . 'dl/js/jquery.pwdstr-1.0.source.js';
$java_scripts[] = $this->relpath . 'dl/js/jquery.tools.min.js';
$java_scripts[] = $this->relpath . 'dl/js/patternlock.js';

$statuslink = $this->statuslink .'&sem='. $content['hashid'];

$java_script = "
	$(document).ready(function() {
		$('#help_autologin').tooltip({ effect: 'slide'});	
		$('#timeout_range').rangeinput();
		$('#notify_pw_range').rangeinput();
		$('#picker').farbtastic('#new_colour');
		$('.options_change').hide();
	
		//$('#check_toggle').hide();
		
		change_options('#settings');
	
		$('#password1').keyup(function(){
			if( $(this).val().length >= 1 ){
				$(this).pwdstr('#time');
				
				if ($('#good_pw').val() == '1'){
					$('#pasword_good').show();
				} else {
					$('#pasword_good').hide();
				}			
				$('#check_toggle').show();
			}else{
				$('#check_toggle').hide();
			}
		}); 
	
		$('#password2').keyup(function(){
			$('#pasword_match_not').show();			
				
			if ($('#password1').val() == $('#password2').val()){
				$('#pasword_match_not').hide();
				$('#pasword_match').show();
			} else {
				$('#pasword_match').hide();
			}
		});
";

if ($this->forgot_active === TRUE){

        $btntext = 'Ändern';
        $forgot_action = 'forgot_change';

        if ( isset($content['forgot_set']) && (!(isset($content['forgot_show_now']))) ){
			$btntext = 'Ansehen';
			$forgot_action = 'show';

			$java_script .= "	
		$('.show_forgot').hide();
				";
        } elseif (isset($content['forgot_show_now'])) {
			$java_script .= "       
                change_options('#forgot');
            ";
		} else {
        	$btntext = 'Sicherheitsfrage neu setzen';
       		$forgot_action = 'forgot_change';			
		}
}

$java_script .= "       
        }); /* END DOCUMENT READY*/
        
        
                function loadingbar () {
                        $('#login_text').load('".$statuslink."');
                        
                        $('#subnav').hide();
                        change_options('#loadingbar');                          
                        var refreshId = setInterval(function() {
                                $('#login_text').load('".$statuslink."');
                                }, 500);
                        $.ajaxSetup({ cache: false });
                        return true;
                }

        ";


?>
<div id="subnav">
	<div style="float: left;">
		<ul>
			<li><a id="settings" class="choose" href="#" onclick="change_options('#settings');"><?=e('settings')?></a></li>	
			<li><a id="colour"  class="choose" href="#" onclick="change_options('#colour');"><?=e('colour')?></a></li>
			<li><a id="passwd" class="choose" href="#" onclick="change_options('#passwd');"><?=e('password')?></a></li>
			<?php if (($this->forgot_active === TRUE) && ($data[0]['use_forgot'] == "yes")): ?>
				<li><a id="forgot" class="choose" href="#" onclick="change_options('#forgot');"><?=e('sec_question')?></a></li>
			<?php endif; ?>
			<?php if ($content['patternlock_active'] == TRUE): ?>
				<li><a id="delete" class="choose" href="#" onclick="change_options('#patternlock');"><?=e('patternlock')?></a></li>
			<?php endif; ?>
		</ul>
	</div>
	<div class="clearer"></div>
</div>
<?php 


$use_autolinkgen = $hide_desc = $hide_comment = $use_forgot = $patternlock_active = '';


$startmodule = (($data[0]['start_module'] == "")) ? $this->defaultstartmodule : $data[0]['start_module'];

if($data[0]['use_autolinkgen'] == "yes")
	$use_autolinkgen = 'checked="checked"';

if($data[0]['hide_desc'] == "yes")
	$hide_desc = 'checked="checked"';

$lang = ($data[0]['lang'] != '') ? $data[0]['lang'] : get_lang();

if($data[0]['hide_comment'] == "yes")
	$hide_comment = 'checked="checked"';

if($content['patternlock_active'] == TRUE)
	$patternlock_active = 'checked="checked"';

$arc_pass_notify_interval = (is_int((int)$data[0]['arc_pass_notify_interval'])) ? $data[0]['arc_pass_notify_interval'] : $this->recommend_password_change_interval;
$arc_pass_notify_interval = $arc_pass_notify_interval/60/60/24;


$lifetime = (isset($data[0]['session_lifetime'])) ? $data[0]['session_lifetime'] : $this->default_session_lifetime;

$lifetime_h = floor($lifetime/60/60);
$lifetime_m = ($lifetime/60) - ($lifetime_h*60);


if($data[0]['use_forgot'] == "yes")
	$use_forgot = 'checked="checked"';

if ($this->forgot_active === TRUE){
	if (!(isset($content['forgot_set']))){
		$content['forgot_question'] = $content['forgot_answer'] = $content['forgot_hint']  = '';
	}
}

?>

<!-- SETTINGS CHANGE --> 

<div id="settings_change" class="options_change">
	<div id="deleteaccount">
		<a style="color: gray;" id="delete" class="choose" href="#" onclick="change_options('#delete');"><?=e('account_delete')?></a>
	</div>
	<form name="options_change_form" method="post" action="<?=link_for('settings', 'change_settings')?>" target="_parent">
		<table>
			<tr><td class="first">
				<?=e('use_homepage')?>
			</td>
			<td><div class="styled-select">
				<select name="start_module">
				<?php
					foreach ($this->registered_start_modules as $module){
						echo '<option value="'.$module.'"';

						if ($module == $startmodule)	
							echo ' selected="selected"';					
										
						echo '>'. strtoupper(e($module)) .'</option>';
					}

				?>
				</select>
				</div>		
			</td></tr>
			<tr>
				<td class="first">
                    <?=e('language')?>
                </td>
                        <td>   <div class="styled-select">
                                <select name="lang">
                                <?php  
                                        foreach ($content['langs'] as $countryname => $countrycode){
                                                echo '<option value="'.$countrycode.'"';

                                                if ($countrycode == $lang)
                                                        echo ' selected="selected"';

                                                echo '>'. $countryname .'</option>';
                                        }
                                ?>
                                </select></div>
                        </td>
            </tr>
            
			<tr>
				<td class="first">
					<?=e('session_timeout_choose_m')?>
				</td>
				<td>
					<input value="<?=$lifetime/60?>" id="timeout_range" type="range" name="session_lifetime" min="1" max="<?=$this->max_session_lifetime/60?>" />
				</td>
			</tr>


                        <tr>   
                                <td class="first">
                                        <?=e('arc_pass_notify_interval')?>
                                </td>
                                <td>   
                                        <input value="<?=$arc_pass_notify_interval?>" id="notify_pw_range" type="range" name="arc_pass_notify_interval" min="<?=$this->min_arc_pass_notify_interval?>" max="<?=$this->max_arc_pass_notify_interval?>" />
                                </td>
                        </tr>




                        <tr><td class="first">
				<?
					$echo_use_forgot = e('use_forgot');
					if ($data[0]['use_forgot'] != "yes")
						$echo_use_forgot = '<div class="info">'.$echo_use_forgot.'</div>';
				
					echo $echo_use_forgot;
				?>
                        </td>
                        <td>   
                                <input type="checkbox" class="checkbox" name="use_forgot" <?=$use_forgot?> />
                        </td></tr>

			<tr>
				<td class="first">
					<?=e('hide_desc')?>
				</td>
				<td>
					<input type="checkbox" class="checkbox" name="hide_desc" <?=$hide_desc?> />
				</td>
			</tr>
			
			<tr>
			<td class="first">
				<?=e('hide_comment')?>
			</td>
			<td>
				<input type="checkbox" class="checkbox" name="hide_comment" <?=$hide_comment?> />
			</td></tr>
			<tr><td class="first">
				<span id="help_autologin"><?=img($this->helpicon, e('help')).'  '.e('use_auto_linkgen')?></span>
				<div class="tooltip_big">
					<?=e('help_autologin')?>
				</div>
			</td>
			<td>
				<input type="checkbox" class="checkbox" name="use_autolinkgen" <?=$use_autolinkgen?> />
			</td></tr>

			<tr><td class="first">
				<?=e('patternlock')?>
			</td>
			<td>
				<input type="checkbox" class="checkbox" name="patternlock_active" <?=$patternlock_active?> />
			</td></tr>

		</table>
		<div>
			<input type="hidden" name="crc_options_change" value="<?=$this->crc?>" /><br />
			<input name="options_change" onclick="loadingbar();" class="button submit-button" value="<?=e('save')?>" type="submit" />
		</div>
	</form>
</div>

<!-- COLOUR CHANGE --> 

<div id="colour_change" class="options_change">
	<br />
	<div class="info"><?=e('colour_info')?></div>
	<br /><br />
	<div id="picker"></div>
	<div id="colour_form">
		<form name="colour_change_form" method="post" action="<?=link_for('settings', 'change_colour')?>" target="_parent">
			<table>
				<tr><td><label for="colour"><?=e('colour')?></label></td><td><input type="text" id="new_colour" name="new_colour" value="<?=$content['colour']?>" /></td></tr>
				<tr><td><label for="username"><?=e('username')?></label></td><td><input type="text" autocomplete = "off" id="username" name="username" value="" /></td></tr>
				<tr><td><label for="password_old"><?=e('password')?></label></td><td><input autocomplete = "off" type="password" class="password" name="password_old" value="" /></td></tr>
				<tr><td></td><td><input onclick="loadingbar();" name="passwd_change" class="button submit-button" value="OK" type="submit" /></td></tr>
			</table>
			<input type="hidden" name="crc_colour_change" value="<?=$this->crc?>" />
		</form>
	</div>
	<div class="clearer"></div>
</div>
 

<!-- PASSWD CHANGE --> 

 <div id="passwd_change" class="options_change" >
	<div id="change_vals">
					<br />
			<div class="info"><?=e('usage_info')?></div>
			<br /><br />
		<form name="username_change_form" method="post" action="<?=link_for('settings', 'change_password')?>" target="_parent">
			<table>
				<tr><td class="first"><label for="username"><?=e('username')?></label></td><td><input type="text" autocomplete = "off" id="username" name="username" value="" /></td><td></td></tr>
				<tr><td class="first"><label for="password_old"><?=e('old_pw')?></label></td><td><input autocomplete = "off" type="password" class="password" name="password_old" value="" /></td><td></td></tr>
				<tr><td class="first"><label for="password_1"><?=e('new_pw')?></label></td><td><input autocomplete = "off" id="password1" type="password" class="password" name="password_1" value="" /></td>
				<td><div id="pasword_good" style="display:none; float:left;"><?=img($this->okicon)?></div></td></tr>
				
				<tr>
					<td class="first"><?=e('password_could_be_hacked_in')?></td>
					<td style="height: 20px;"> 
						 <div id="check_toggle">
							<div id="time_descr">
								</div><div id="time" class="info"></div><div class="clearer"></div><div id="time_descr2">
							</div>
						</div>
						 <input id="good_pw" type="hidden" value="0" /> 
					</td>
					<td></td>
				</tr>
				
				<tr><td class="first"><label for="password_2"><?=e('new_pw_repeat')?></label></td><td><input autocomplete = "off" id="password2" type="password" class="password" name="password_2" value="" /></td>
				<td>
					<div id="pasword_match" style="display:none; float:left;"><?=img($this->okicon)?></div>
					<div id="pasword_match_not" style="display:none; float:left;"><?=img($this->minusicon)?></div>
					<div class='clearer'></div>
				</td>
				</tr>

				<tr><td class="first"></td><td><input type="hidden" name="crc_passwd_change" value="<?=$this->crc?>" />
					<input onclick="loadingbar();" name="passwd_change" class="button submit-button" value="<?=e('change_pw')?>" type="submit" /></td><td></td></tr>
			</table>
		</form>
	</div>
</div>

<!-- PATTERNLOCK -->
<?php if ($content['patternlock_active'] == TRUE): ?>
	<div id="patternlock_change" class="options_change">
	 	<input onclick="$('#patternlock_change').css('height', '380px'); $('.patternlockcontainer').toggle(); $('#save_patternlock').toggle(); $('#reset_patternlock').toggle(); $('#patternlockcontainer_show').toggle();" id="patternlockcontainer_show" class="button" value="<?=e('patternlock_change')?>" type="submit" />
	 	
	 	<form style="float: left;" name="options_change_form" method="post" action="<?=link_for('settings', 'change_settings')?>" target="_parent">
	 		<input style="display: none;" onclick="loadingbar();" id="save_patternlock" class="button" value="<?=e('save')?>" type="submit" />
	 		<input autocomplete = "off" value="" type="text" id="patternlock" name="patternlock" class="patternlock" />
	 	</form>
	 	<input style="float: left; display: none;" onclick="reset_patternlock();" id="reset_patternlock" class="button" value="<?=e('patternlock_reset')?>" type="submit" />
		<div class="clearer"></div>
		
	</div>
<?php endif; ?>


<!-- ACCOUNT DELETE -->
 <div id="delete_change" class="options_change">
	<form name="delete_change" method="post" action="<?=link_for('settings', 'delete_me')?>" target="_parent">
		<div class="info">
		<?=e('delete_account_confirm')?>
		<br />
		<?=e('account_really_delete')?>
		</div>
		<br /><br />
		<table>
			<tr><td class="first"><label for="username"><?=e('username')?></label></td><td><input type="text" autocapitalize="off" autocorrect="off" id="username" name="username" value="" style="direction:ltr" /></td></tr>
			<tr><td class="first"><label for="password_old"><?=e('password')?></label></td><td><input type="password" autocomplete="off" class="password" name="password_old" value="" style="direction:ltr" /></td></tr>
			<tr><td class="first"></td><td><input type="hidden" name="crc_delete_user" value="<?=$this->crc?>" /><input onclick="loadingbar();" name="delete_user" class="button submit-button" value="<?=e('delete')?>" type="submit" /></td></tr>
		</table>
	</form>		
</div>

<!-- LOADINGBAR -->
 <div id="loadingbar_change" class="options_change">
	<div id="login_stat">
		<div id="login_img" style="float: left;"><?=img($this->loading)?></div>
		<div id="login_text" style="float: left; margin: 8px 10px; color:white;"></div>
		<div class="clearer"></div>
	</div>		
</div>

<!-- FORGOT -->
<?php if (($this->forgot_active === TRUE) && ($data[0]['use_forgot'] == "yes")): ?>
<div id="forgot_change" class="options_change">
	<br />
	
	<?php if (!(isset($content['forgot_set']))): ?>
        	<?$msg=e('no_question_set')?>
        	<div class="info"><?=e('no_question_set')?></div>
        	<br />
        	<?=img($this->infoicon).'&nbsp;'.e('forgot_info1')?><br />
        	<?=img($this->infoicon).'&nbsp;'.e('forgot_info2')?><br />
        	<br />
        	<?=img($this->infoicon).'&nbsp;'.e('forgot_info3')?><br />
        	<?=img($this->infoicon).'&nbsp;'.e('forgot_info4')?><br />
        	<br />
	<?php elseif (!(isset($content['forgot_show_now']))): ?>
		<div class="info"><?=e('question_set_but_not_enc')?></div>
	<?php elseif (isset($content['forgot_show_now'])): ?>
		<div class="info"><?=e('question_set_and_shown')?></div>
	<?php endif; ?>
	
	<br />
	<form name="forgot_change" method="post" action="<?=link_for('settings', $forgot_action)?>" target="_parent">
        <table>
		<?php if (isset($content['forgot_show_now'])): ?>
        	<tr class="show_forgot"><td class="first"><label for="question"> <?=e('your_question')?></label></td><td><input type="text" id="question" name="question" value="<?=$content['forgot_question']?>" /></td></tr>
                <tr class="show_forgot"><td class="first"><label for="answer"> <?=e('enter_answer')?></label></td><td><input type="text" id="answer" name="answer" value="<?=$content['forgot_answer']?>" /></td></tr>
                <tr class="show_forgot"><td class="first"><label for="hint"> <?=e('your_pass_hint')?></label></td><td><input type="text" id="hint" name="hint" value="<?=$content['forgot_hint']?>" /></td></tr>
		<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
		<?php endif; ?>
                <tr><td class="first"><label for="username_forgot_change"> <?=e('username')?></label></td><td><input type="text" autocapitalize="off" autocorrect="off" id="username_forgot_change" name="username_forgot_change" value="" /></td></tr>
                <tr><td class="first"><label for="password_forgot_change"> <?=e('password')?></label></td><td><input type="password" autocapitalize="off" autocorrect="off" id="password_forgot_change" name="password_forgot_change" value="" /></td></tr>
		<tr><td class="first"></td><td><input type="hidden" name="crc_change_forgot" value="<?=$this->crc?>" /><input onclick="loadingbar();" name="change_forgot" class="button submit-button" value="<?=$btntext?>" type="submit" /></td></tr> 
	</table>
	</form>
	<br />
     	
</div>
<?php endif; ?>


<?php
if ($content['lastupdated_to_old'] === TRUE){
	$msg = e('last_pw_change', array(strftime(e('dateopts'), $content['lastupdated'])));
	$msg .= '<div class="error">'.e('change_pw_suggest').'</div>';
}
?>

<!-- Language things for Jquery PWDString Ext -->
<input type="hidden" id="lesssecond" value="<?=e('lesssecond')?>" />
<input type="hidden" id="onesecond" value="<?=e('onesecond')?>" />
<input type="hidden" id="seconds" value="<?=e('seconds')?>" />
<input type="hidden" id="onemminute" value="<?=e('onemminute')?>" />
<input type="hidden" id="minutes" value="<?=e('minutes')?>" />
<input type="hidden" id="onehour" value="<?=e('onehour')?>" />
<input type="hidden" id="hours" value="<?=e('hours')?>" />
<input type="hidden" id="oneday" value="<?=e('oneday')?>" />
<input type="hidden" id="days" value="<?=e('days')?>" />
<input type="hidden" id="onemonth" value="<?=e('onemonth')?>" />
<input type="hidden" id="months" value="<?=e('months')?>" />
<input type="hidden" id="oneyear" value="<?=e('oneyear')?>" />
<input type="hidden" id="years" value="<?=e('years')?>" />
