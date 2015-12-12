<?php
$java_script = '
$(document).ready(function() {

	$("#searchresult").hide();
	$("#searchform input[name=q]").keyup(function(){
		if( $(this).val().length >= '.$this->min_search_chars.' ){
			$("#searchresult").show().html("suche...");

			$.ajax({
				url: "'. link_for('search','live') .'",
				data:"q="+$(this).val(),
				type:"POST",
				success: function(data) {
					$("#searchresult").html(data)
				}
			});
		}else{
			$("#searchresult").show().html("'.e('enter_chars', array($this->min_search_chars)).'");
		}
	});
	$(document.body).click(function(){
		$("#livesearchresult").hide();
	});

});
';
?>

<div id="search">
	<form id="searchform">
        	<input type="text" name="q" />
                <div id="searchresult"></div>
        </form>
</div>
