<?php

class ScannerPage implements Page {
	
	public static function displayPage() {
		InsertHeader("Scan Image");
		
		$CANNERS=json_decode(file_exists("config/scanners.json") ? file_get_contents("config/scanners.json") : '[]');
		
		ScannerPage::securityCheck($CANNERS);
		
		if(strlen($SAVEAS)>0){ # Save settings to conf file
			if(strlen($SET_SAVE)>0){
				$ACTION="Save Set";
				$setting=array("scanner" => $SCANNER, "source" => $SOURCE, "duplex" => $DUPLEX, "quality" => $QUALITY, "size" => $SIZE ,"ornt" => $ORNT, "mode" => "$MODE", "bright" => $BRIGHT, "contrast" => $CONTRAST, "rotate" => $ROTATE, "scale" => $SCALE, "filetype" => $FILETYPE, "lang" => $LANG);
				if(file_exists("config/settings.json")){
					$file=json_decode(file_get_contents("config/settings.json"));
					$file->{$SET_SAVE}=$setting;
					SaveFile("config/settings.json",json_encode($file));
				}
				else{
					if(!SaveFile("config/settings.json",json_encode(array($SET_SAVE => $setting)))){
						Print_Message("Permission Error:","<code>$user</code> does not have permission to write files to the <code>".getcwd()."/config</code> folder.<br/>$notes",'center');
					}
				}
			}
		}

		if(count($CANNERS)==0){ # Add scanners to scanner list
			Print_Message("No Scanners Found",'There aren\'t any scanners setup yet! Go to the <a href="index.php?page=Config">config page</a> to setup scanners.','center');
		}
		else{
			if(file_exists('config/settings.json'))
				$file=file_get_contents('config/settings.json');
			else
				$file='{}';
			
			$tpl = new Template('scan');
		}

		if($ACTION=="Scan Image"){# Check to see if scanner is in use
			$SCAN_IN_USE=$CANNERS[$SCANNER]->{"INUSE"};
			if($SCAN_IN_USE==1){
				Print_Message("Scanner in Use","The scanner you are trying to use is currently in use. Please try again later...",'center');
				$ACTION="Do Not Scan";
			}
			else if($WIDTH===0&&$HEIGHT===0&&$ROTATE===0)
				addRuler();
		}
		else if(strlen($ACTION)==0||strlen($SAVEAS)>0)
			echo '<script type="text/javascript">addRuler();scanReset();</script>';

		if(strlen($ACTION)>0) # Only update values back to form if they aren't empty
			Put_Values();
		Footer('');

		if($ACTION=="Scan Image"){ # Scan Image!
			if(is_nan($SCANNER)){
				Print_Message("Error:","<code>$SCANNER</code> is not a number, you must be trying to attack the server",'center');
				quit();
			}
			$CANDIR="/tmp/scandir$SCANNER";

			if(is_dir($CANDIR)){ # Make sure we can save the scan
				$trash=scandir($CANDIR);
				unset($trash[0]);unset($trash[1]);// Delete ./ and ../ from the list
				foreach($trash as $key)
					@unlink($key);
				rmdir($CANDIR);
				if(is_dir($CANDIR)){
					Print_Message("Permission Error:","<code>$user</code> does not have permission to delete <code>$CANDIR</code>.<br/>".
						"This can be easly fixed by running the following command at the Scanner Server.<br/><code>rm -r $CANDIR</code><br/>".
						"Once you have done that you can press F5 (Refresh) to try again with your prevously entered settings.",'center');
					quit();
				}
			}

			if(!@mkdir("$CANDIR")){
				Print_Message('Error',"Unable to create directory $CANDIR.<br>Why does <code>$user</code> not have permission?",'center');
				quit();
			}

			$sizes=explode('-',$SIZE);
			if((!validNum(Array($SCANNER,$WIDTH,$HEIGHT,$X_1,$Y_1,$BRIGHT,$CONTRAST,$SCALE,$ROTATE)))||
			   (count($sizes)!=2&&$SIZE!=='full')||
			   (!in_array($MODE,explode('|',$CANNERS[$SCANNER]->{"MODE-$SOURCE"})))||
			   (!in_array($SOURCE,explode('|',$CANNERS[$SCANNER]->{"SOURCE"})))||
			   (!in_array($DUPLEX,is_bool($CANNERS[$SCANNER]->{"DUPLEX-$SOURCE"})?array(true,false):explode('|',$CANNERS[$SCANNER]->{"DUPLEX-$SOURCE"})))||
			   ($FILETYPE!=="txt"&&$FILETYPE!=="png"&&$FILETYPE!=="tiff"&&$FILETYPE!=="jpg")){
				Print_Message("No, you can not do that","Input data is invalid and most likely an attempt to run malicious code on the server. <i>Denied</i>",'center');
				quit();
			}
			else if((!is_numeric($sizes[0])||!is_numeric($sizes[1]))&&$SIZE!=='full'){
				Print_Message("No, you can not do that","Input data is invalid and most likely an attempt to run malicious code on the server. <i>Denied</i>",'center');
				quit();
			}

			# Scanner in Use
			$CANNERS[$SCANNER]->{"INUSE"}=1;
			if(!SaveFile("config/scanners.json",json_encode($CANNERS))){
				Print_Message("Permission Error:","<code>$user</code> does not have permission to write files to the <code>".getcwd()."/config</code> folder.<br/>$notes",'center');
				quit();
			}
			$X=0;
			$Y=0;
			# Get Device
			$DEVICE=shell($CANNERS[$SCANNER]->{"DEVICE"});

			$scanner_w=$CANNERS[$SCANNER]->{"WIDTH-$SOURCE"};
			$scanner_h=$CANNERS[$SCANNER]->{"HEIGHT-$SOURCE"};

			$lastORNT=Get_Values('ornt0');
			if($lastORNT!=$ORNT&&$lastORNT!=null&&$SIZE!="full"){
				$WIDTH=0;
				$HEIGHT=0;
			}
			# Set size & orientation of scan
			if($WIDTH!==0&&$HEIGHT!==0){// Selected scan
				if($SIZE=="full"){
					$TRUE_W=$scanner_w;
					$TRUE_H=$scanner_h;
				}
				else{
					if($ORNT=="vert"){
						$TRUE_W=$sizes[0];
						$TRUE_H=$sizes[1];
					}
					else{
						$TRUE_W=$sizes[1];
						$TRUE_H=$sizes[0];
					}
				}
				$WIDTH=$WIDTH/$M_WIDTH*$TRUE_W;
				$HEIGHT=$HEIGHT/$M_HEIGHT*$TRUE_H;
				$X=$X_1/$M_WIDTH*$TRUE_W;
				$Y=$Y_1/$M_HEIGHT*$TRUE_H;
				$SIZE_X=$WIDTH;
				$SIZE_Y=$HEIGHT;
			}
			else if($SIZE=="full"){// full scan
				$SIZE_X=$scanner_w;
				$SIZE_Y=$scanner_h;
			}
			else if($sizes[0]<=$scanner_w&&$sizes[1]<=$scanner_h&&$sizes[1]<=$scanner_w&&$sizes[0]<=$scanner_h){// fits both ways
				if($ORNT!="vert"){
					$SIZE_X=$sizes[1];
					$SIZE_Y=$sizes[0];
				}
				else{
					$SIZE_X=$sizes[0];
					$SIZE_Y=$sizes[1];
				}
			}
			else if($sizes[0]<=$scanner_w&&$sizes[1]<=$scanner_h){//fits tall way
				$SIZE_X=$sizes[0];
				$SIZE_Y=$sizes[1];
			}
			else if($sizes[1]<=$scanner_w&&$sizes[0]<=$scanner_h){//fits wide way
				$SIZE_X=$sizes[1];
				$SIZE_Y=$sizes[0];
			}
			else{
				Print_Message("Sorry...","The scan page should not have offered this page size as it does not fit in your scanner.<br/>That paper will not fit in the scanner running a full scan.".
					"<br/>Scanner width is $scanner_w mm<br/>Scanner height is $scanner_h mm".
					"<br/>Paper width is ".$sizes[0]." mm<br/>Paper height is ".$sizes[1]." mm",'center');
				$SIZE="-x $scanner_w -y $scanner_h";
				$SIZE_X=$scanner_w;
				$SIZE_Y=$scanner_h;
			}
			$LAMP='';
			/*if($CANNERS[$SCANNER]->{'LAMP'}===true){
				$LAMP='--lamp-switch=yes --lamp-off-at-exit=yes ';
			}*/

			if(!is_bool($CANNERS[$SCANNER]->{"DUPLEX-$SOURCE"})){
				$DUPLEX="--adf-mode $DUPLEX ";
			}
			else if($CANNERS[$SCANNER]->{"DUPLEX-$SOURCE"}===true){
				if($DUPLEX==true)
					$DUPLEX='--duplex=yes ';
				else
					$DUPLEX='--duplex=no ';
			}
			else
				$DUPLEX='';
			$OURCE=($SOURCE=='Inactive')?'':"--source ".shell($SOURCE)." ";
			if(!is_null($CANNERS[$SCANNER]->{"UUID"})){// Bug #13
				$DEVICE2=uuid2bus($CANNERS[$SCANNER]);
				$CANNERS[$SCANNER]->{"DEVICE"}=$DEVICE2;
				$DEVICE=shell($DEVICE2);
			}
			$cmd="scanimage -d $DEVICE $OURCE-l $X -t $Y -x $SIZE_X -y $SIZE_Y $DUPLEX--resolution $QUALITY --mode ".shell($MODE)." $LAMP--format=$RAW";
			if($SOURCE=='ADF'||$SOURCE=='Automatic Document Feeder') # Multi-page scan
				exe("cd $CANDIR;$cmd --batch",true);// Be careful with this, doing this without a ADF feeder will result in scanning the flatbed over and over, include --batch-count=3 for testing
			else # Single page scan
				exe("$cmd > ".shell("$CANDIR/scan_file$SCANNER.$RAW"),false);

			if(file_exists("$CANDIR/scan_file$SCANNER.$RAW")){
				if(Get_Values('size')=='full'&&filesize("$CANDIR/scan_file$SCANNER.$RAW")==0){
					exe("echo 'Scan Failed...'",true);
					exe("echo 'Maybe this scanner does not report it size correctly, maybe the default scan size will work it may or may not be a full scan.'",true);
					exe("echo 'If it is not a full scan you are welcome to manually edit your $here/config/scanners.json file with the correct size.'",true);
					@unlink("$CANDIR/scan_file$SCANNER.$RAW");
					exe("echo 'Attempting to scan without forcing full scan'",true);
					exe("scanimage -d $DEVICE --resolution $QUALITY --mode ".shell($MODE)." $LAMP--format=$RAW > ".shell("$CANDIR/scan_file$SCANNER.$RAW"),false);
				}
			}

			if(count($CANNERS)>1&&isset($DEVICE2)){
				$CANNERS=json_decode(file_get_contents("config/scanners.json"));
				$CANNERS[$SCANNER]->{"DEVICE"}=$DEVICE2;// See bug 13
			}
			
			$CANNERS[$SCANNER]->{"INUSE"}=0;
			SaveFile("config/scanners.json",json_encode($CANNERS));

			$startTime=time();
			$files=scandir($CANDIR);
			$GMT=0;
			
			if(strlen($TimeZone)>0){
				date_default_timezone_set($TimeZone);
			}
			else if(ini_get('date.timezone')==='' && version_compare(phpversion(), '5.1', '>=')){
				date_default_timezone_set('UTC');
				$GMT=intval(exe('date +%z',true))*36;
				exe('echo "Warning, Guessing Time Zone:\n\tGuessed as GMT '.($GMT/60/60).'.\n\tdate.timezone is not set in your /etc/php5/apache2/php.ini file.\n\tIt is probably set on line 880.\n\tThere is also a override in '.getcwd().'/config.ini on line 11."',true);
			}
			
			for($i=2,$ct=count($files);$i<$ct;$i++){
				$SCAN=shell("$CANDIR/".$files[$i]);

				# Dated Filename for scan image & preview image
				$FILENAME=date("M_j_Y~G-i-s",filemtime("$CANDIR/".$files[$i])+$GMT);
				$S_FILENAME="Scan_$SCANNER"."_"."$FILENAME.$FILETYPE";
				$P_FILENAME="Preview_$SCANNER"."_"."$FILENAME.jpg";

				# Adjust Brightness
				if($BRIGHT!="0"||$CONTRAST!="0"){
					if($MODE=='Lineart'){
						exe("convert $SCAN -brightness-contrast '$BRIGHT".'x'."$CONTRAST' -depth 16 $SCAN",true);
						exe("convert $SCAN -monochrome -depth 1 $SCAN",true);
					}
					else{
						exe("convert $SCAN -brightness-contrast '$BRIGHT".'x'."$CONTRAST' $SCAN",true);
					}
				}

				# Rotate Image
				if($ROTATE!="0"){
					exe("convert $SCAN -rotate '$ROTATE' $SCAN",true);
				}

				# Scale Image
				if($SCALE!="100"){
					exe("convert $SCAN -scale '$SCALE%' $SCAN",true);
				}

				# Generate Preview Image
				exe("convert $SCAN -scale '450x471' ".shell("scans/thumb/$P_FILENAME"),true);

				# Convert scan to file type
				if($FILETYPE=="txt"){
					$S_FILENAMET=substr($S_FILENAME,0,strrpos($S_FILENAME,'.'));
					exe("convert $SCAN -fx '(r+g+b)/3' ".shell("/tmp/_scan_file$SCANNER.tif"),true);
					exe("tesseract ".shell("/tmp/_scan_file$SCANNER.tif").' '.shell("scans/file/$S_FILENAMET")." -l ".shell($LANG),true);
					unlink("/tmp/_scan_file$SCANNER.tif");
					if(!file_exists("scans/file/$S_FILENAMET.txt"))//in case tesseract fails
						SaveFile("scans/file/$S_FILENAMET.txt","");
				}
				else{
					exe("convert $SCAN -alpha off ".shell("scans/file/$S_FILENAME"),true);
				}
				@unlink("$CANDIR/".$files[$i]);
			}
			
			@rmdir($CANDIR);
			$endTime=time();

			# Remove Crop Option / set lastScan
			if(($WIDTH!==0&&$HEIGHT!==0)||$ROTATE!==0)
				$strip=true;
			
			else{
				setcookie('lastScan',json_encode(Array(
					"raw"=>$S_FILENAME,"preview"=>$P_FILENAME,"fields"=>Array("scanner"=>$SCANNER,
						"source"=>$SOURCE,		"quality"=>$QUALITY,	"duplex"=>$DUPLEX,
						"size"=>$SIZE,			"ornt"=>$ORNT,			"mode"=>$MODE,
						"bright"=>$BRIGHT,		"contrast"=>$CONTRAST,	"scale"=>$SCALE,
						"filetype"=>$FILETYPE,	"lang"=>$LANG,			"set_save"=>$SET_SAVE
					)
				)),time()+86400,substr($_SERVER['PHP_SELF'],0,strlen(end(explode('/',$_SERVER['PHP_SELF'])))*-1),$_SERVER['SERVER_NAME']);
			}
			
			$ORNT=($ORNT==''?'vert':$ORNT);
			
			echo "<script type=\"text/javascript\">var ornt=document.createElement('input');ornt.name='ornt0';ornt.value='$ORNT';ornt.type='hidden';document.scanning.appendChild(ornt);".
				($ROTATE!="0"?"var p=document.createElement('p');p.innerHTML='<small>Changing orientation will void select region.</small>';getID('opt').appendChild(p);":'').
				"$(document).ready(function(){document.scanning.scanner.disabled=true;".(isset($strip)?"stripSelect();":'')."});</script>";
				
			# Check if image is empty and post error, otherwise post image to page
			if(!file_exists("scans/thumb/$P_FILENAME")){
				Print_Message("Could not scan",'<p style="text-align:left;margin:0;">This is can be cauesed by one or more of the following:</p>'.
					'<ul><li>The scanner is not on.</li><li>The scanner is not connected to the computer.</li>'.
					'<li>You need to run the <a href="index.php?page=Access%20Enabler">Access Enabler</a>.</li>'.
					(file_exists("/tmp/scan_file$SCANNER.$RAW")?"<li>Removing <code>/tmp/scan_file$SCANNER.$RAW</code> may help.</li>":'').
					'<li><code>'.$user.'</code> does not have permission to write files to the <code>'.getcwd().'/scans</code> folder.</li>'.
					'<li>You may have to <a href="index.php?page=Config">re-configure</a> the scanner.</li></ul>'.$notes,'left');
			}
			else{
				Update_Links($S_FILENAME,$PAGE);
				Update_Preview("scans/thumb/$P_FILENAME");
			}
			
			echo '<script type="text/javascript">if(document.scanning.scanner.childNodes.length>1)document.scanning.reset.disabled=true;</script>';
			
			if($ct>3)
				Print_Message("Info",'Multiple scans made, only displaying last one, go to <a href="index.php?page=Scans&amp;filter=3&amp;origin='.$SCANNER.'&amp;T2='.($startTime-1).'&T1='.($endTime+1).'">Scanned Files</a> for the rest','center');
		}
		
		checkFreeSpace($FreeSpaceWarn);
	}
	
	private static function securityCheck($CANNERS) {
		if(strlen($SAVEAS) > 0 || $ACTION == "Scan Image"){
			$langs=findLangs();
			if(!validNum(Array($SCANNER,$BRIGHT,$CONTRAST,$SCALE,$ROTATE))||!in_array($LANG,$langs)||!in_array($QUALITY,explode("|",$CANNERS[$SCANNER]->{"DPI-$SOURCE"}))){//security check
				Print_Message("No, you can not do that","Input data is invalid and most likely an attempt to run malicious code on the server <i>denied</i>",'center');
				Footer('');
				quit();
			}
		}
	}
}