<?php 
$java_scripts[] = $this->relpath . 'dl/js/jquery.autosize-min.js';
$java_script = '

$(function(){
	$(".arc_textarea").autosize({append: "\n"});
});

';


$java_script .= '$(document).ready(function() {
                        ';

if (($this->request['action'] == 'favs') && (is_array($data))){
	foreach ($data as $id => $row){
		$java_script .= 'load_portal('.$id.');
		';
	}
}




if ($this->search_active === TRUE) {
$java_script .= '
    $("#search #q").focus();

    $("#s_fo").submit(function(e) {
	search_cat();	    
	//e.preventDefault();
	return false;
    });




	var autocomplete = $("#q").on("keyup", delayRequest);
	function dataRequest() {
		var searchString = $("#q").val();
		if(window.lastsearch == undefined) {
			window.lastsearch = "#";
		}
		if ((searchString.length >= '.$this->min_search_chars.') && (window.lastsearch != searchString)){
		        search_cat();
			window.lastsearch = searchString;
	        	return false;
		}

	}

	function delayRequest(ev) {
	    if(delayRequest.timeout) {
	        clearTimeout(delayRequest.timeout);
	    }

	    var target = this;

	    delayRequest.timeout = setTimeout(function() {
	        dataRequest.call(target, ev);
	    }, 800); // 200ms delay
	}

';
}


$java_script .= '
        });';

?>
<?php if ($this->request['action'] != 'favs'): ?>
	
	 <div id="subnav">
	 	<ul>
			<li><a class="choose" href="#" onclick="$('#add_category').toggle();$('#categories').toggle();$('#search').toggle();"><?=e('new_category')?></a></li>
			<?php if (is_array($data)) : ?>
				<li><a class="choose" href="<?=link_for('export','printview')?>"><?=e('print_view')?></a></li>
			<?php endif; ?>
		</ul>
	 </div>
	
<div id="kat-top">

	<?php if ($this->search_active === TRUE): ?>
	<br>
	<div id="search">
		<div id="searchform">
			<form id="s_fo" name="s_fo" method="POST" action="<?=link_for('search', 'live')?>">
				<input type="text" name="q" id="q"/>
				<img id="search_icon" src="<?=$this->loupeicon?>" class="search-icon" onclick="search_cat();" />
				<img id="reset_search_icon" src="<?=$this->minusicon?>" style="display: none;" />
				<input type="submit" class="button" style="display: none;" />
			</form>
			<br>
			<div id="searchresult" class="info"></div>
		</div>
	</div>
	<?php endif; ?>
	<div class="clearer"></div>
	<div id="add_category">
		<form name="add_cat" method="post" action="<?=link_for('categories', 'add')?>" target="_parent">
			<table>
				<tr><td><label for="category_add_field"><?=e('category')?></label></td><td><input id="category_add_field" type="text" class="text" name="category" value="" style="direction:ltr" /></td></tr>
				<?php if(!(defined('HIDE_DESC'))): ?>
					<tr><td><label for="desc_add_field"><?=e('desc')?></label></td><td><input id="desc_add_field" type="text" class="text" name="desc" value="" style="direction:ltr" /></td></tr>
				<?php endif; ?>
				<tr><td></td><td><input type="hidden" name="crc_add_cat" value="<?=$this->crc?>" /><input id="login-button" name="add_cat" class="button submit-button" value="<?=e('new_category')?>" type="submit" /></td></tr>
			</table>
		</form>
	</div>
</div>

<?php else: ?>
	<div id="subnav">&nbsp;</div>
<?php endif; ?>
<?php 
if (is_array($data)){
	
	echo '<div id="categories">';
	$i=0;
	foreach ($data as $id => $row){
		$kc = ($i==0) ? 'categoryfirst' : 'categorylist';
		echo '
		<div class="category '.$kc.'" id="cat_form_'.$id.'">
			<div class="categorieItemTop">
				<div class="categorieItemTopItem show">
					<img class="category_' . $id . '" onclick="load_portal(' . $id . ');" alt="bild" title="'.e('open_cat').'" src="' . $this->searchicon . '" />
					<img class="change changecat" src="' . $this->configicon . '" onclick="change_cat_form(\''. $id .'\');" alt="'.e('change').'" title="'.e('change').'" />
					<img src="' . $this->plusicon . '" class="plus pluscat" onclick="$(\'#add_portal_form_'. $id .'\').toggle();" alt="'.e('portal_create').'" title="'.e('portal_create').'" />
				</div>
				
				<div class="categorieItemTopItem">
					<input id="input_name_'.$id.'" class="in" type="text" name="category" value="'. $row['category'] .'" readonly="readonly" />
				</div>
		';

		if (!(defined('HIDE_DESC')))
			echo '<div class="categorieItemTopItem"><input id="input_desc_'.$id.'" class="in" type="text" name="desc" value="'. $row['desc'].'" readonly="readonly" /></div>';
	
		echo '

					
				<div class="categorieItemTopItem">
					<input type="hidden" name="id_cat" value="' . $id . '" />
				</div>
					

					
				
				<div class="categorieItemTopItem">
					<img onclick="save_cat(\''. $id .'\');" class="save savecat" src="' . $this->okicon . '" alt="'.e('save').'" title="'.e('save').'" style="display: none;" />
				</div>
				<div class="categorieItemTopItem">
					<img onclick="del_cat(\''. $id .'\');" class="delete deletecat" src="' . $this->minusicon . '" alt="'.e('delete').'" title="'.e('delete').'" style="display: none;" />
				</div>
				<div id="category_' . $id . '" class="categoryholder category_' . $id . '"></div>
				<div class="clearer"></div>
			</div>
		';
		
		
		echo '
			<div class="add_portal" id="add_portal_form_'. $id .'" style="display: none;">
			<h1>'.$row['category'].'</h1>
			';
		
		
		$desc = (defined('HIDE_DESC')) ? '<td></td><td></td>' : '<td class="first">'.e('desc').'</td><td><input id="input_portaldesc_'.$id.'" class="in" type="text" name="desc" value="" /></td>';
			
		$input_type_link = ($content['use_autolinkgen']) ? '<textarea rows="5" cols="5" class="in linkinput arc_textarea"' : '<input type="text" value="" class="in linkinput"';
		$input_type_link_post = ($content['use_autolinkgen']) ? '></textarea>' : '/>';

		$autologin = ($content['use_autolinkgen']) ? '<input type="hidden" id="add_portal_autologin_'. $id .'" value="0" /><img onclick="change_autologin('.$id.', \'add_portal\');" class="autologin" src="' . $this->autologinicon . '" alt="'.e('autologin_toggle').'" title="'.e('autologin_toggle').'" />' : '';
		
		echo '
			<table>
				<tr>
					<td class="first">Name</td>
					<td><input id="input_portalname_'.$id.'" class="in" type="text" name="name" value="" /></td>
					'.$desc.'
				</tr>
								
				<tr>
					<td class="first">'.e('login').'</td>
					<td><input id="input_portal_login_'.$id.'" type="text" name="portal_login" value="" /></td>
					<td>'.e('password').'</td><td><input id="input_portal_pass_'.$id.'" type="text" name="portal_pass" value="" /></td>
					<td></td>
				</tr>
			
			</table>
			
			<div class="add_portal_link">
			
				<div class="linktext">
					'.e('link').'
					<br /><br />'.
					$autologin.'
					<br />
					<br />
					<img class="gen_passwd" onclick="gen_passwd_new('. $id .');" src="' . $this->genpasswdicon . '" alt="'.e('gen_passwd').'" title="'.e('gen_passwd').'">
				</div>

				'.$input_type_link.' id="input_link_'.$id.'" name="link" '.$input_type_link_post.'
				<div class="clearer"></div>
			</div>
				
			<input type="button" class="button" onclick="add_portal(\''. $id .'\');" value="'.e('portal_create').'" />
			<input type="button" class="button" onclick="$(\'#add_portal_form_'. $id .'\').toggle();" value="'.e('cancel').'" />

			</div>
			<div class="clearer"></div>
		';
		
		//echo '<div id="category_' . $id . '" class="category_' . $id . '"></div>';
		echo '</div>'; //end 1 cat

		$i++;
	}
	echo '</div>';//end all cat
	echo '<input type="hidden" id="action" value="'.$this->request['action'].'" />';
}
if ((count($data) == 0) && ($this->request['action'] == 'favs'))
	echo e('no_favs');
?>
<?php if ((is_array($data)) && ($this->request['action'] != 'favs')) : ?>

<?php endif; ?>
