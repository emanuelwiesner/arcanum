<?
function getlangnav ($langs) {
    foreach ($langs as $lang_name => $langkey){
		echo ($langkey == get_lang()) ? '<div class="lang active">' : '<div class="lang">';
        echo '<a href="'.link_for('lang', 'change', TRUE).'&amp;code='.$langkey.'&amp;origin='.base64_encode($_SERVER['REQUEST_URI']).'">'.$lang_name.'</a>';
        echo '</div>';
    }
}
?>

		<? if ($this->modulename == 'login'): ?>
						<form name="login" id="login" method="post" action="<?=link_for('login')?>" target="_parent" autocomplete="off">
							<table id="loginform">
								<tr>
									<td><input type="text" tabindex = "1" maxlength="50" id="username" name="username" autocomplete="off" value=""/></td>
									<td class="right">
										<div id="langnav">
											 <?=getlangnav($this->arc_langs)?>
				               			</div>
									</td>
								</tr>
								<tr>
									<td>	
										<input type="password" tabindex = "2" maxlength="50" id="password" name="password" autocomplete="off" value=""/>
									</td>
									<td class="right">
										<input type="submit" class="button" tabindex = "3" name="login_button" value="<?=e('login')?>" id="button"/>
									</td>
								</tr>
							</table>
						</form>
						
				<?php elseif ($this->modulename == 'register'): ?>
										<div id="langnav">
											 <?=getlangnav($this->arc_langs)?>
				               			</div>
		
				<?php elseif (@$auth == TRUE): ?>					
					<script type="text/JavaScript">
						$(document).ready(function() {
					
							var refreshId = setInterval(function() {
								timer_regenerate();
								}, <?=$this->timer_refresh_rate?>);
							$.ajaxSetup({ cache: false });	
					
						});
					</script>
						<div id="logout">
							<!-- <form method="post" action="<?=link_for('login', 'logout')?>" target="_parent">
							-->
								<input type="submit" class="button" name="login_button" value="Logout" id="logoutbutton" onclick="doarclogout();" />
							<!-- </form> -->
						</div>
						
						<div id="timercap">
							<div id="timerinfo">autologout in&nbsp;</div>
							<div id="timer"><?=$content['timer_display_content']?></div>
							<div class="clear"></div>
						</div>
						
				<? endif; ?>
