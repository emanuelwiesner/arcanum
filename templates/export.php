<?php
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

if (is_array($content)){
	
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
