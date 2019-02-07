<?php

function html($X){
	return htmlspecialchars($X);
}

function url($X){
	return rawurlencode($X);
}

function js($X){
	return str_replace("\n",'\\n',addslashes($X));
}

function InsertHeader($title) { # Spit out HTML header
	$page=$GLOBALS['PAGE'];
	$GLOBALS['DarkPicker']=$DarkPicker=isset($_COOKIE['darkPicker'])?$_COOKIE['darkPicker']=='true':$GLOBALS['DarkPicker'];
	include "res/inc/header.php";
	return $path;
}

function Footer($path) { # Spit out HTML footer
	$title=$GLOBALS['PAGE'];
	include "res/inc/footer.php";
}

function Get_Values($name){
	if(isset($_REQUEST[$name])){
		$name=$_REQUEST[$name];
		if(is_numeric($name))
			if(intval($name)==floatval($name))
				return intval($name);
			else
				return floatval($name);
		else if(strtolower($name)==='true')
			return true;
		else if(strtolower($name)==='false')
			return false;
		return $name;
	}
	else
		return null;
}

function shell($X){
	return escapeshellarg($X);
}

function Put_Values() { # Update values back to form (There is no redo for cropping)
	echo '<script type="text/javascript">config('.json_encode((object)array(
		'scanner'=>$GLOBALS['SCANNER'],
		'source'=>$GLOBALS['SOURCE'],
		'quality'=>$GLOBALS['QUALITY'],
		'duplex'=>$GLOBALS['DUPLEX'],
		'size'=>$GLOBALS['SIZE'],
		'ornt'=>$GLOBALS['ORNT'],
		'mode'=>$GLOBALS['MODE'],
		'bright'=>$GLOBALS['BRIGHT'],
		'contrast'=>$GLOBALS['CONTRAST'],
		'rotate'=>$GLOBALS['ROTATE'],
		'scale'=>$GLOBALS['SCALE'],
		'filetype'=>$GLOBALS['FILETYPE'],
		'lang'=>$GLOBALS['LANG'],
		'set_save'=>$GLOBALS['SET_SAVE']
	)).');</script>';
}

function Print_Message($TITLE,$MESSAGE,$ALIGN) { # Add a Message div after the page has loaded
	$TITLE=js(html($TITLE));
	$MESSAGE=js($MESSAGE);
	$ALIGN=js(html($ALIGN));
	include "res/inc/message.php";
}

function Update_Preview($l) { # Change the Preview Pane image via JavaScript
	echo '<script type="text/javascript">'.
		'getID("preview_img").childNodes[0].childNodes[0].src="'.js($l).'";'.
		'</script>';
}

function addRuler(){
	echo '<script type="text/javascript">addRuler();</script>';
}

function genIconLinks($config,$file,$isBulk){
	// The Last Scan button is unique to the scan page, it is in res/main.js and res/inc/scan.php
	if($config===null)
		$config=(object)array();
	$sURL=url(substr($file,5));
	$sJS=html(js(substr($file,5)));
	$URL=url($file);
	$JS=html(js($file));
	$icons=(object)array(
		'download'=>(object)array(
			'href'=>"download.php?file=$URL",
			'disable'=>isset($config->{'download'}),
			'tip'=>'Download'
		),
		'zip'=>(object)array(
			'href'=>"download.php?file=$URL&amp;compress",
			'disable'=>isset($config->{'zip'}),
			'tip'=>'Download Zip',
			'bulk'=>"bulkDownload(this,'zip')"
		)
		,
		'pdf'=>(object)array(
			'href'=>'#',
			'onclick'=>"return PDF_popup('$sJS',false);",
			'disable'=>isset($config->{'pdf'}),
			'tip'=>'Download PDF',
			'bulk'=>"PDF_popup(filesLst)"
		),
		'print'=>(object)array(
			'href'=>"print.php?file=$URL",
			'onclick'=>$GLOBALS['Printer'] % 2 == 0?'return true':"return PDF_popup('$sJS',true)",
			'target'=>'_blank',
			'disable'=>isset($config->{'print'}),
			'tip'=>'Print',
			'bulk'=>$GLOBALS['Printer'] % 2 == 0?"bulkPrint(this)":"PDF_popup(filesLst,true)"
		),
		'del'=>(object)array(
			'href'=>"index.php?page=Scans&amp;delete=Remove&amp;file=$sURL",
			'onclick'=>"return delScan('$sJS',true)",
			'disable'=>isset($config->{'del'}),
			'tip'=>'Delete',
			'bulk'=>"bulkDel()"
		),
		'edit'=>(object)array(
			'href'=>"index.php?page=Edit&amp;file=$sURL",
			'disable'=>isset($config->{'edit'}),
			'tip'=>'Edit'
		),
		'view'=>(object)array(
			'href'=>"index.php?page=View&amp;file=$URL",
			'disable'=>isset($config->{'view'}),
			'tip'=>'View',
			'bulk'=>"bulkView(this)"
		),
		'upload'=>(object)array(
			'href'=>'#',
			'onclick'=>"return upload('$JS');",
			'disable'=>isset($config->{'upload'}),
			'tip'=>'Upload to Imgur',
			'bulk'=>"bulkUpload()"
		),
		'email'=>(object)array(
			'href'=>'#',
			'onclick'=>"return emailManager('$JS');",
			'disable'=>isset($config->{'email'}),
			'tip'=>'Email',
			'bulk'=>"emailManager('Scan_Compilation')"
		)
	);
	if($GLOBALS['PAGE']=='Scan'){
		$click=false;
		if(isset($_COOKIE['lastScan'])&&!isset($config->{'recent'})){
			$cookie=json_decode($_COOKIE['lastScan']);
			if(file_exists("scans/file/".$cookie->{"raw"})&&file_exists("scans/thumb/".$cookie->{"preview"}))
				$click="return lastScan(".html(json_encode($cookie)).",this,'".html(js(genIconLinks((object)array('recent'=>0),$cookie->{'raw'},false)))."')";
			else
				setcookie('lastScan','',0);
		}
		$icons->{'recent'}=(object)array(
			'href'=>'#',
			'onclick'=>(is_bool($click)?'false':$click),
			'disable'=>is_bool($click),
			'tip'=>'Last Scan'
		);
	}
	$html='';
	foreach($icons as $icon => $link){
		if($link->{'disable'})
			$html.='<span class="tool icon '.$icon.'-off"><span class="tip">'.$link->{"tip"}.' (Disabled)</span></span>';
		else{
			$html.='<a class="tool icon '.$icon.'"';
			if($isBulk)
				$html.=" onclick=\"return ".$link->{"bulk"}."\"";
			else{
				foreach($link as $attr => $val){
					if($attr=='disable')
						break;
					$html.=" $attr=\"$val\"";
				}
			}
			$html.='><span class="tip">'.$link->{"tip"}.'</span></a>';
		}
	}
	return $html;
}

function Update_Links($l,$p) { # Change the Preview Pane image links via JavaScript
	echo '<script type="text/javascript">'.
		'getID("preview_links").innerHTML="<h2>'.html($l).'</h2><p>'.
		js(genIconLinks($p=="Edit"?(object)array('edit'=>'disable'):null,$l,false)).
		'</p>";</script>';
}

function SaveFile($file,$content){// @ Suppresses any warnings
	$file=@fopen($file,'w+');
	@fwrite($file,$content);
	@fclose($file);
	if(is_bool($file))
		return $file;
	return true;
}

function checkFreeSpace($X){
	$dirs=Array('file','thumb');
	foreach($dirs as $key => $val){
		$pace=disk_free_space("scans/$val")/1024/1024;
		if($pace<$X)// There is less than X MB of free space
			Print_Message("Warning: Low Disk Space","There is only ".number_format($pace)." MB of free space in <code>".getcwd()."/scans/$val</code>, please delete some scan(s) if any.<br/>Low disk space can cause really bad problems.",'center');
	}
	return $pace;
}

function fileSafe($l){
	if(strpos($l,"/")>-1)
		$l=substr($l,strrpos($l,"/")+1);
	return $l;
}

function validNum($arr){
	for($i=0,$m=count($arr);$i<$m;$i++){
		if(!is_numeric($arr[$i]))
			return false;
	}
	return true;
}

function exe($shell,$force){
	$output=str_replace("\\n","\n",shell_exec($shell.($force?' 2>&1':'')).($force?'':'The output of this command unfortunately has to be suppressed to prevent errors :(\nRun `sudo -u '.$GLOBALS['user']." $shell` for output info"));
	$GLOBALS['debug'].=$GLOBALS['here'].'$ '.$shell."\n".$output.(substr($output,-1)=="\n"?"":"\n");
	return $output;
}

function debugMsg($msg){// Good for printing a quick message during testing
	Print_Message("Debug Message",$msg,'center');
}

function findLangs(){
	$tess="/usr/share/tesseract-ocr/tessdata";// This is where tesseract stores it language files
	$langs="/usr/share/doc";// This is where documentation is stored
	if(is_dir($tess)){
		$langs=array();
		$tess=scandir($tess);
		for($i=2,$max=count($tess);$i<$max;$i++){
			$pos=strpos($tess[$i],'.');
			if($pos){
				$tess[$i]=substr($tess[$i],0,strpos($tess[$i],'.',$pos));
				if(!in_array($tess[$i],$langs)){
					array_push($langs,$tess[$i]);
				}
			}
		}
	}
	else if(is_dir($langs)){
		$langs=explode("\n",substr(exe("ls ".shell($langs)." | grep 'tesseract-ocr-' | sed 's/tesseract-ocr-//'",true),0,-1));
	}
	else{
		Print_Message("Tesseract Error:","Unable to find any installed language files or documentation.<br/>You can edit lines 145 and or 146 of <code>".getcwd()."/index.php</code> with the correct location for your system.","center");
		$langs=array();
	}
	return $langs;
}

function uuid2bus($d){// Bug #13
	$id=$d->{"UUID"};
	$d=$d->{"DEVICE"};
	$data=exe("lsusb -d ".shell($id)." # See Bug #13",true);
	if(strlen($data)==0)
		return $d;// Scanner must not be connected
	$bus=substr($data,strpos($data,"Bus ")+4,3);
	$dev=substr($data,strpos($data,"Device ")+7,3);
	$pos=strpos($d,"libusb:")+7;
	return substr($d,0,$pos)."$bus:$dev".substr($d,$pos+9);
}

function quit(){
	echo '<script type="text/javascript">Debug("'.js(html($GLOBALS['debug'])).js(html($GLOBALS['here']."$ ")).'",'.(isset($_COOKIE["debug"])?$_COOKIE["debug"]:'false').');';
	if($GLOBALS['CheckForUpdates']){
		$VER=$GLOBALS['VER'];
		$file="config/gitVersion.txt";
	 	$time=is_file($file)?filemtime($file):time()/2;
	 	if($time+3600*24<time())
			echo "updateCheck('$VER',null);";
		else{
			$file=file_get_contents($file);
			if(version_compare($file,$VER)==1)
				echo "updateCheck('$file',true);";
		}
	}
	die('</script></body></html>');
}

?>