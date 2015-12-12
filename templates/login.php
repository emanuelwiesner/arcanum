<? 

$java_scripts[] = $this->relpath . 'dl/js/patternlock.js';
$css_s[] = $this->relpath . 'dl/css/patternlock.css';

$java_script = "
$(document).ready(function() {
	$('#login #username').focus();
	
    $('#noscript').hide();
    var contentHeight = $('#content').height();
	
	$('#content').css('height', 47+'px');

	$('#button').click(function(){
		var username = $('#login #username').val();  
	    var password = $('#login #password').val(); 
		
		if ((username != '') && (password != '')){
			$('#login_text').html('".e('checking_login')."');
			$('#login_stat').show();
			
			$('#content').animate({
				height: contentHeight+'px',
				paddingBottom: '105px'
			}, 500);
			
			window.loaded_patternlock = $('#patternlock').val();
			doarclogin();
		}
		return false;
	});
	

});";

	$msg = (isset($_GET['msg'])) ? unserialize($_GET['msg']) : $content;
?>

<div id="login_stat">
	<div id="login_img" style="float: left;">
		<?=img($this->loading)?>
	</div>
	<div id="login_text" style="float: left; margin: 8px 10px; color:white;">
		
	</div>
	<div class="clearer"></div>
</div>

<input type="password" id="patternlock" name="patternlock" class="patternlock" />
