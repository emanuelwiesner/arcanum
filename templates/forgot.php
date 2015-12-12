<?php 
$java_script = "
$(document).ready(function() {
    $('#question_row').hide();
    $('#answer_row').hide();
    $('#hint_row').hide();
    $('#username').focus();
});

	$(document).keypress(function(e) {
	    if(e.which == 13) {
	        walk_forgot ();
	    }
	});    

function walk_forgot (){
	forgot_form = '#forgot';
	
	var username = $(forgot_form + ' #username').val();
	var answer = $(forgot_form + ' #answer').val();
	var question = $(forgot_form + ' #question').html();
	
	var dataString = 'username='+ username + '&answer='+ answer;
	
	$.ajax({  
		 type: 'POST',  
		 url: '".link_for('forgot', 'yrly')."',  
		 data: dataString, 
		 success: function (reqCode) {
    			if (answer == ''){
    				$(forgot_form + ' #question').html(reqCode);
				$(forgot_form + ' #username').attr('readonly', 'readonly');
				$(forgot_form + ' #send_username').hide();
    				$('#question_row').show();
				$('#answer_row').show();
    			} else if ((answer != '') && (question != '')) {
				$(forgot_form + ' #answer').attr('readonly', 'readonly');
				$(forgot_form + ' #send_answer').hide();
    				$(forgot_form + ' #hint').html(reqCode);
    				$('#hint_row').show();
    			} else {
				set_message(reqCode);
			}
		 },
		error: function (xhr, status, errorThrown) {
			set_message(xhr.responseText);
		}
	}); 
	
}

";
?>

<div id="forgot">
	<b><?=e('activities_are_being_logged')?></b>	
	<br /><br />
	<table>
		<tr id="username_row">
			<td><label for="question"><?=e('enter_username')?></label></td>
			<td><input type="text" id="username" name="username" value="" style="direction:ltr" /></td>
			<td><img id="send_username" src="<?=$this->nexticon?>" alt="<?=e('next_step')?>" title="<?=e('next_step')?>" onclick="walk_forgot();" /></td>
		</tr>
		<tr id="question_row">
			<td><label for="question"><?=e('your_question')?></label></td>
			<td><div id="question"></div></td>
			<td>&nbsp;</td>
		</tr>
		<tr id="answer_row">
			<td><label for="answer"><?=e('enter_answer')?></label></td>
			<td><input type="text" id="answer" name="answer" value="" style="direction:ltr" /></td>
			<td><img id="send_answer" src="<?=$this->nexticon?>" alt="<?=e('next_step')?>" title="<?=e('next_step')?>" onclick="walk_forgot();" /></td>
		</tr>
		<tr id="hint_row">
			<td><label for="hint"><?=e('your_pass_hint')?></label></td>
			<td><div id="hint"></div></td>
			<td>&nbsp;</td>
		</tr>				
	</table>

</div>
