<?
$alph = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z', '123');
$java_scripts[] = $this->relpath . 'dl/js/jquery.autosize-min.js';
$java_script = '
$(document).ready(function(){
	$(".arc_textarea").autosize({append: "\n"});
';

if ($this->search_active === TRUE) {
$java_script .= '
    $(".category").hide();
    $("#search #q").focus();

    $("#s_fo").submit(function(e) {
        search_memo();       
        return false;
    });

        var autocomplete = $("#q").on("keyup", delayRequest);
        function dataRequest() {
                var searchString = $("#q").val();
                if(window.lastsearch == undefined) {
                        window.lastsearch = "#";
                }
                if ((searchString.length >= '.$this->min_search_chars.') && (window.lastsearch != searchString)){
                        search_memo();
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
if (!(is_array($data)))
	$java_script .= "$('#memoalphabet').toggle();";
}
?>

<div id="subnav">
	<ul>
		<li><a id="newmemo" class="choose" href="#" onclick="$('#memoalphabet').toggle();$('#add_memo').toggle();$('#categories').toggle();$('#search').toggle();"><?=e('new_memo')?></a></li>
	</ul>
</div>

<div id="kat-top">
        <div id="add_memo" style="display: none;">
                <form name="add_memo" method="post" action="<?=link_for('memo', 'add')?>" target="_parent">
                        <table>
                                <tr><td><label for="title_in"><?=e('memo_title')?></label></td><td><input type="text" id="title_in" class="text" name="title" value="" style="direction:ltr" /></td></tr>
                                <tr><td><label for="memo_in"><?=e('memo_note')?></label></td><td> <textarea cols="5" rows="5" id="memo_in" class="text newmemonote arc_textarea" name="note"></textarea> </td></tr>
                                <tr><td></td><td><input type="hidden" name="crc_add_cat" value="<?=$this->crc?>" /><input id="login-button" name="add_cat" class="button submit-button" value="<?=e('new_memo')?>" type="submit" /></td></tr>
                        </table>
                </form>
        </div>
        <?php if (($this->search_active === TRUE) && is_array($data)): ?>
        <br>
        <div id="search" style="float: left;">
                <div id="searchform">
                        <form id="s_fo" name="s_fo" method="POST" action="<?=link_for('search', 'memo')?>">
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
		<?php
			if (is_array($data)) {
				echo '<div id="memoalphabet">';
				$availl = array();
				foreach ($data as $id => $row){
					$title = strtolower(trim($row['title']));
					if (preg_match('%^[a-zA-Z]%', $title)) {
						$data[$id]['firstl'] = $title[0];
					//} elseif (preg_match('%^[0-9]%', $title)) {
					} else {
						$data[$id]['firstl'] = '123';
					} //else {
					//	$data[$id]['firstl'] = '+';
					//}
					if (!(in_array($data[$id]['firstl'], $availl)))
						$availl[] = $data[$id]['firstl'];
				}
				foreach ($alph as $l) {
					$class = $onclick = '';
					if (in_array($l, $availl)){
						$class = ' letteravail';
						$onclick = ' onclick="$(\'.category\').hide();$(\'.categoryfirstletter_'.$l.'\').show();"';
					}
					echo '<div class="letter'.$class.'"'.$onclick.'>'.$l.'</div>';
				}
			echo '</div>';
			}
		?>
	<div class="clearer"></div>	
</div>

<?
if (!(is_array($data))){

	echo '<br /><br />'.e('no_memo');

} elseif (is_array($data)){
	
	echo '<div id="categories">';
	$i=0;
	foreach ($data as $id => $row){

		if (defined('EXPAND_MEMOS'))
			$java_script .= "\n$('#note_".$id."').toggle();";

		$kc = ($i==0) ? 'categoryfirst' : 'memolist';
		$kc = 'memolist'; //always, we re-designed memos
		echo '
		<div class="category categoryfirstletter_'.$row['firstl'].' '.$kc.'" id="cat_form_'.$id.'">
			<div class="categorieItemTop">
				
				
				<div class="categorieItemTopItem show">
					<img class="category_' . $id . '" onclick="$(\'#note_' . $id . '\').toggle();" alt="bild" title="'.e('open_cat').'" src="' . $this->searchicon . '" />
					<img class="change changecat" src="' . $this->configicon . '" onclick="change_memo_form(\''. $id .'\');" alt="'.e('change').'" title="'.e('change').'" />
				</div>
				
				
				<div class="categorieItemTopItem">
					<input id="memo_title_'.$id.'" class="in" type="text" name="category" value="'. $row['title'] .'" readonly="readonly" />
				</div>
				
				
				<div class="categorieItemTopItem">
					<input style="color: #999;" id="input_desc_'.$id.'" class="in" type="text" name="desc" value="'. date(e('dateopts_num'), $row['updated']).'" readonly="readonly" />
				</div>
				
				
				<div class="categorieItemTopItem">
					<img onclick="save_memo(\''. $id .'\');" class="save savecat" src="' . $this->okicon . '" alt="'.e('save').'" title="'.e('save').'" style="display: none;" />
				</div>
				<div class="categorieItemTopItem">
					<img onclick="del_memo(\''. $id .'\');" class="delete deletecat" src="' . $this->minusicon . '" alt="'.e('delete').'" title="'.e('delete').'" style="display: none;" />
				</div>
				
				
				<div id="category_' . $id . '" class="categoryholder category_' . $id . '">
					<div class="portals">
						<div id="note_' . $id . '" class="portal black" style="display: none;">
							<div class="portalsholder memonote" style="float: left; overflow: hidden;">
								
								
								<textarea cols="5" rows="5" id="memo_note_'.$id.'" class="in arc_textarea" name="name" readonly="readonly">' . $row['note'] . '</textarea> 


							</div>
							<div class="clearer"></div>
						</div>
					</div>
				</div>
				<div class="clearer"></div>
			</div>

		</div>'; //end 1 cat

		$i++;
	}
	echo '</div>';//end all cat
}
$java_script .= "\n});";
?>
