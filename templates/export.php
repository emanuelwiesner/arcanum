<?php unset($content['timer_display_content']); if (!(count($content) > 0)): ?>
<?php
$statuslink = $this->statuslink .'&sem='. $content['hashid'].'&upload=1';
$java_script = "
        $(document).ready(function() {
                change_options('#exportall');
	});
                function loadingbar () {
                        $('#subnav').hide();
                        change_options('#loadingbar');
                        return true;
                }

";
?>

<div id="subnav">
	<ul>
		<li><a id="exportall" class="choose" href="#" onclick="change_options('#exportall');"><?=e('exportall')?></a></li>
		<li><a id="importall" class="choose" href="#" onclick="change_options('#importall');"><?=e('importall')?></a></li>
	</ul>
</div>


<div id="exportall_change" class="options_change">
	<br />
	<?=e('exportall_info')?>
	<br /><br />
	<form id="exportall_now" action="<?=link_for('export', 'exportall')?>" method="post">
		<table>		
				<tr><td class="first"><?=e('password')?></td> <td><input type="password" tabindex = "1" id="password" name="export_password1" autocomplete="off" value=""/></td></tr>
				<tr><td class="first"><?=e('password_repeat')?></td> <td><input type="password" tabindex = "1" id="password" name="export_password2" autocomplete="off" value=""/></td></tr>
				<tr><td></td><td><input onclick="loadingbar();" name="change_forgot" class="button submit-button" value="<?=e('download')?>" type="submit" /></td></tr>		
		</table>
	</form>
</div>


<div id="importall_change" class="options_change">
	<br />
	<?=e('importall_info')?>
	<br /><br />
	<form id="importall_file" enctype="multipart/form-data" action="<?=link_for('export', 'importall')?>" method="post">	
		<table>	
				<tr><td class="first"><?=e('password')?></td> <td><input type="password" tabindex = "1" id="password" name="import_password" autocomplete="off" value=""/></td></tr>
				<tr><td class="first"><?=e('file')?></td><td><input name="thefile" type="file"></td></tr>
				<tr><td></td><td><input onclick="loadingbar();" class="button" type="submit" value="<?=e('upload')?>"></td></tr>		
		</table>
	</form>
</div>
<div id="loadingbar_change" class="options_change">
        <div id="login_stat">
                <div id="login_img" style="float: left;"><?=img($this->loading)?></div>
                <div id="login_text" style="float: left; margin: 8px 10px; color:white;"></div>
                <div class="clearer"></div>
        </div>
</div>
<?php endif; ?>
<?php
if (is_array($content) && (count($content) > 0)){
	
	$java_script = '
	function printDiv(divName) {
		 col = $("body").css("background-color");
		 $("body").css("background-color", "white");
		 var printContents = document.getElementById(divName).innerHTML;
		 var originalContents = document.body.innerHTML;
		 document.body.innerHTML = printContents;
		 

		 portmargin = $(".portals").css("margin");
		 exportinnermargin = $(".exportinner").css("margin-top");	
		 $(".portals").css("margin", "15px 0 0 15px");
		 $(".exportinner").css("margin-top", "0px");
		 
		 window.print();
		 
		 document.body.innerHTML = originalContents;
		 $(".portals").css("margin", portmargin);
		$(".exportinner").css("margin-top", exportinnermargin);
		 $("body").css("background-color", col);
		
	}
	';
	
	$exp_content = $content;
	unset($exp_content['timer_display_content']);

	echo img($this->printicon, e('print'), e('print'),'','',"printDiv('printableArea');")." ";
	echo '<a href="#" onclick="printDiv(\'printableArea\');">'.e('print').'</a>';
	echo br().br();
	
	
	echo '<div id="printableArea">';
	foreach ($exp_content as $category) {

			echo '<div class="categories">';
			echo '<b class="info">'.$category['category'] .'</b>'. br();
			
			if (isset($category['portals'])){
				if (is_array($category['portals'])){
					foreach ($category['portals'] as $portals){
						echo '<div class="portals">';
						echo $portals['name'];
					
					
						foreach ($portals['arcanums'] as $arcanums){
							echo '<div class="portals exportinner" style="margin-top: 0px;">';
							echo htmlspecialchars("[".$arcanums['portal_login'] . "] : [" . $arcanums['portal_pass'] . "]");
						
							echo "</div>";
						
						}
					
						echo "</div>";
					
					}
				}
			}
			echo "</div><br><br>";
	}
	echo '</div>';
}
?>
