<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<!--###LANG###-->" lang="<!--###LANG###-->">
<head>

<title>Arcanum - <?=e($modulename)?></title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="<?=$this->favicon?>" type="image/png; charset=binary" />
<meta name="autor" content="Emanuel Wiesner" />
<meta name="language" content="<!--###LANG###-->" />
<meta name="robots" content="index, follow, noodp, noydir" />
<meta name="revisit-after" content="14 days" />
<meta name="document-class" content="Completed" />
<meta name="document-rights" content="Copyrighted Work" />
<meta name="document-type" content="Public" />
<meta name="document-rating" content="General" />
<meta name="document-distribution" content="Global" />
<meta name="cache-control" content="Private" />

<!--###CSS###-->

<!--###JAVASCRIPT###-->

</head>
<body <?=($this->modulename == 'login')?'id="homepage"':''; ?>>
	<div id="shadower">
		<div id="outer">
			<? if(($this->https === FALSE)&&($this->https_warning === TRUE)){ 
				echo '<div id="non_https_warning" class="error">'.e('non_https_warning').'</div>';
			}
			?>
			<div id="header">
				<div id="modulename"><?=e($modulename)?></div>
				<div id="nav">
					<!--###MENU###--> 
				</div>
				<div id="actionpanel">
					<!--###ACTIONPANEL###--> 
				</div>
			</div>
			<div id="content">
				<!--###CONTENT###-->
			</div>
			<div id="footer">
				<div id="msg">
					<!--###MSG###-->
				</div>
			</div>
		</div>
	</div>
</body>
</html>
