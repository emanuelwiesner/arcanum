<?php if ((@$this->inv_mode == TRUE) && ((@$_GET['code'] == "") || ($_GET['action'] != "invite")) ): ?>
	
	<?=e('only_inv')?>

<?php else: ?>

<?php 
if (isset($content)) {
	if (is_array($content)) {
		echo "ausloggen!";
		exit;
	} else {
		die($content);
	}
}


$css_s[] = $this->relpath . 'dl/css/farbtastic.css';
$java_scripts[] = $this->relpath . 'dl/js/farbtastic.js';
$java_scripts[] = $this->relpath . 'dl/js/jquery.pwdstr-1.0.source.js';

$java_script = "
$(document).ready(function() {     
	$('#picker').farbtastic('#colour');
        
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
    
    $('#noscript').hide();
    
    $('#check_toggle').hide();
    $('.reg_change').hide();
    $('#register_form_1').show();
})
";
?>
<?php if (@$this->inv_mode == TRUE): ?>
	<div id="subnav">
		<!-- <?=e('inv_link_id')?> --> 
		<input type="hidden" id="inv_id" name="inv_id" value="<?=$_GET['code']?>" readonly="readonly" />
	</div>
<?php endif; ?>

<noscript>
<div id="noscript" class="error"><?=e('js_disabled')?></div>
</noscript>

<div id="register">

			<?php if (@$this->inv_mode == TRUE): ?>
				<div class="info"><?=e('someone_invited')?><br><?=e('register_legal')?><br><?=e('username_not_changeable')?></div>
				<br><br><br>
			<?php endif; ?>
			
			<table>
				<colgroup>
    					<col width="50">
    					<col width="50">
    					<col width="50">
  				</colgroup>
			
				<tr><td class="first"><label for="username"><?=e('username')?></label></td><td><input type="text" autocomplete = "off" id="username" name="username" value="" style="direction:ltr" /></td><td></td></tr>
				<tr><td class="first"><label for="password_1"><?=e('new_pw')?></label></td><td><input autocomplete = "off" id="password1" type="password" class="password" name="password_1" value="" style="direction:ltr" /></td>
				<td><div id="pasword_good" style="display:none; float:left;"><?=img($this->okicon)?></div></td></tr>
				
				<tr style="height: 40px;">
					<td class="first"><?=e('password_could_be_hacked_in')?></td>
					<td colspan="2"> 
						 <div id="check_toggle">
							<div id="time_descr">
								</div><div id="time" class="info"></div><div class="clearer"></div><div id="time_descr2">
							</div>
						</div>
						 <input id="good_pw" type="hidden" value="0"> 
					</td>
				</tr>
				
				<tr><td class="first"><label for="password_2"><?=e('new_pw_repeat')?></label></td><td><input autocomplete = "off" id="password2" type="password" class="password" name="password_2" value="" style="direction:ltr" /></td>
				<td>
					<div id="pasword_match" style="display:none; float:left;"><?=img($this->okicon)?></div>
					<div id="pasword_match_not" style="display:none; float:left;"><?=img($this->minusicon)?></div>
					<div class='clearer'></div>
				</td>
				</tr>
				
				
				<?php if ($this->inv_mode != TRUE): ?>
				<!-- CAPTCHA -->
				<tr>
					<td class="first" id="reloadcaptcha">
						<img onclick="reloadcaptcha();" src="<?php echo $this->reloadcaptchaicon; ?>" alt="<?=e('new_captcha')?>" title="<?=e('new_captcha')?>">
					</td>
					<td id="captcha"><img id="captcha_img" src="<?=link_for('getcaptcha')?>" title="" alt=""></td>
					<td><input type="hidden" id="captcha_count" name="captcha_count" value=""/></td>
				</tr>
				<tr>
					<td class="first"><label for="captcha"><?=e('enter_captcha_text')?></label></td>
					<td><input type="text" id="captchatext" name="captcha" value="" style="direction:ltr" /></td>
					<td></td>
				</tr>				
				<?php else: ?>
				
				<tr><td></td><td></td><td></td></tr>
				<tr><td></td><td></td><td></td></tr>
				<tr><td></td><td></td><td></td></tr>	
				
				<?php endif; ?>

				<tr>
					<td class="first"><input onclick="do_register();" class="button submit-button" value="<?=e('register')?>" type="submit" /></td>
					<td></td>
					<td></td>
				</tr>				
			</table>
			
			<div id="colour_choose">
				<div>
					<input type="text" id="colour" name="colour" value="<?=$this->default_colour?>" style="direction:ltr" />
				</div>
				<br>
				<div id="picker"></div>
				<!-- <div class="info"><?=e('choose_fav_colour')?></div> -->
			</div>
				
			<div class="clearer"></div>
</div>

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
<?php endif; ?>
