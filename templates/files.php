<?php 
$statuslink = $this->statuslink .'&sem='. $content['hashid'].'&upload=1';
$java_script = "
		$(document).ready(function() {
			change_options('#showallfiles');	
		});
		
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

if (is_array($data)){
	$categories = array();
	foreach ($data as $id => $elem){
		
		if (array_key_exists('category' ,$elem)){
			$id_cat = explode('c_', $id);
			$categories[$id_cat[1]] = $elem;
			unset($data[$id]);
		} else {
			$sortarray[$data[$id]['category_name']][$id] = $data[$id];
		}
	}
	
	if (isset($sortarray)){
		ksort($sortarray);
		$data = $sortarray;
		unset($sortarray);
	}
}


if (is_array($data)){
	//echo debug('data',$data);
	$size = 0;
	$out = '<div id="categories">';

	$id = 0;
	foreach ($data as $cat => $files){
		$kc = ($id == 0) ? 'categoryfirst' : 'categorylist';

		 $out .= '
                <div class="category '.$kc.'" id="cat_form_'.$id.'">
                        <div class="categorieItemTop">
                                
                                <div class="categorieItemTopItem show">
                                <img class="category_' . $id . '" onclick="$(\'#note_' . $id . '\').toggle();" alt="bild" title="'.e('open_cat').'" src="' . $this->searchicon . '" />
                                </div>                      
                          
                                <div class="categorieItemTopItem">
                                        <input id="memo_title_'.$id.'" class="in" type="text" name="category" value="'. $cat . '" readonly="readonly" />
                                </div>
				<div id="category_' . $id . '" class="categoryholder category_' . $id . '">
                                        <div class="portals">
                                                <div id="note_' . $id . '" class="portal black" style="display: none;">
                                                        <div class="portalsholder memonote" style="float: left; overflow: hidden;">
                					        <table>

									<colgroup>
										<col width="535px">
										<col>
										<col width="90px">
										<col>
										<col>
									<!--	
									<tr>
				                        		<th class="first" width="485px">'.e('filename').'</th>
									<th>'.e('date').'</th>
						                        <th>'.e('size').'</th>
						                        <th>'.e('download').'</th>
						                        <th>'.e('delete').'</th>-->';
                 
                 if (!(defined('HIDE_COMMENT')))
                        $out .= '<col>';

		$out .=  '</colgroup>';

                foreach ($files as $file_id => $row){
                        $size +=  $row['size'];
		
			$down = ($row['date'] != '') ? ' <img src="' . $this->historyicon . '" title="'.e('file_uploaded', array(strftime(e('dateopts'),$row['date']))).'">' : '';
                        $out .= '<tr>
					<td class="first">'.$row['name'].'</td>

					<td class="table-center">'.$down.'</td>

                                        <td>'.get_h_size($row['size']).'</td>

					
                                        <td class="table-center">
						<a href="'. link_for("files", "download") . '&id_file='.$file_id.'">
							<img src="' . $this->downloadicon . '" title="'.e('download').' '.$row['name'].'" >
						</a>
					</td>
					
                                        <td class="table-center">
						<a onclick="return confirm(\''.e('file_really_delete', array($row['name'])).'\');" href="'. link_for("files", "del") . '&id_file='.$file_id.'">
							<img src="' . $this->minusicon . '" title="'.$row['name'].' '.e('delete').'">
						</a>
					</td>
				';

                        if (!(defined('HIDE_COMMENT')))
                                $out .= '<td class="table-center">'.$row['comment'].'</td>';

                        $out .= '</tr>';
                }

		$out .= '</table>
                                                        </div>
                                                        <div class="clearer"></div>
                                                </div>
                                        </div>
                                </div>
                                <div class="clearer"></div>
                        </div>
                </div>'; //end 1 cat
		$id++;
	}
	$out .= '</div>'; //end all cat


	$sizeM = get_h_size($size);
	if ($this->inv_mode === TRUE) {
		$this->max_data_storage += $this->storage_per_inv * $content['invs'];
		$this->max_data_storage = ($this->max_data_storage > $this->storage_upper_limit) ? $this->storage_upper_limit : $this->max_data_storage;

	}

	$maxM = $this->max_data_storage/1024/1024;
	
	$alt=  e('file_space_stat', array($sizeM, $maxM), array(1,2));
	$pc = ceil(($size/$this->max_data_storage)*100);
	
	$memory = '<div id="file_used_bar_outer"><div id="file_used_bar" style="width: '.$pc.'%;"></div></div>';
}


function get_h_size($size){
	$sizeM = round($size/1024/1024, 1);
	if ($sizeM == 0){
		$unity = "KB";
		$sizeM = ceil($size/1024);
	} else {
		$unity = "MB";
	}
	return ($sizeM.' '.$unity);
}
?>

<?php if (isset($categories)): ?>

 	<script type="text/javascript" charset="utf-8">
	  $(document).ready(function() {
	    $('#add').hide();

	    $('#sendfile').submit(function(){
	        $('input[type=submit]', this).attr('readonly', 'readonly');
	    });
	    
	  });
	 </script>
	 
	 
<div id="subnav">
	 <ul>
	 	<li>
			<a id="showallfiles" class="choose" href="#" onclick="change_options('#showallfiles');
			<?
				for ($i=0;$i<=$id;$i++)
					echo "$('#note_".$i."').toggle();";	
			?>	
			"><?=e('showallfiles')?></a>
		</li>
		<li><a id="newfile" class="choose" href="#" onclick="change_options('#newfile');"><?=e('file_uploding')?></a></li>
	</ul>
</div>

<div id="newfile_change" class="options_change" style="display: none;">
	<form id="sendfile" enctype="multipart/form-data" action="<?=link_for('files', 'add')?>" method="post">	
		<table>
			<tr>
				<td><input type="hidden" name="crc_add_file" value="<?=$this->crc?>" /><?=e('category')?></td>
				<td><div class="styled-select">
					<select name="cat">
					<?php 
						asort($categories);
						foreach ($categories as $id => $cat){		
							echo '<option value="'.$id.'">'.$cat["category"].'</option>';			
						}
					?>
					</select>
					</div>
				</td>
			</tr>
			<?php if (!(defined('HIDE_COMMENT'))): ?>
				<tr><td><?=e('comment')?></td><td><input name="comment" type="text"></td></tr>
			<?php endif; ?>
			<tr><td><?=e('file')?></td><td><input type="hidden" name="MAX_FILE_SIZE" value="<?=$this->max_file_size?>"><input name="thefile" type="file"></td></tr>
			<tr><td></td><td><input class="button" type="submit" onclick="loadingbar();" value="<?=e('upload')?>"></td></tr>
		</table>
	</form>
</div>

<div id="showallfiles_change" class="options_change" style="display: none;">
	<?php 
	if (isset($pc)){
		if ($size > 0){		
			$msg = $alt.$memory;
		}
		echo $out;
	}
	?>
</div>

 <div id="loadingbar_change" class="options_change">
	<div id="login_stat">
		<div id="login_img" style="float: left;"><?=img($this->loading)?></div>
		<div id="login_text" style="float: left; margin: 8px 10px; color:white;"></div>
		<div class="clearer"></div>
	</div>		
</div>

<?php else: ?>

<?=e('files_no_cat')?>

<?php endif; ?>
