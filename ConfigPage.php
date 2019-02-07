<?php

class ConfigPage implements Page {
	
	public static function displayPage() {
		InsertHeader("Configure");
		if($ACTION=="Delete-Setting"){ # Delete saved scan settings
			$val=Get_Values('value');
			if($val==null){
				if(file_exists("config/settings.json")){
					if(@unlink("config/settings.json"))
						Print_Message("Deleted","All saved scan settings have been removed!","center");
					else
						Print_Message("Error","Unable to delete <code>".getcwd()."config/settings.json</code>","center");
				}
				else
					Print_Message("Unable to remove saved scanner settings:","There are no settings to remove, therefore that action can not be completed","center");
			}
			else{
				$file=json_decode(file_get_contents("config/settings.json"));
				unset($file->{$val});
				if(!SaveFile("config/settings.json",json_encode($file)))
					Print_Message("Permission Error:","<code>$user</code> does not have permission to write files to the <code>".getcwd()."/config</code> folder.<br/>$notes",'center');
				else
					Print_Message("Deleted","<code>".html($val)."</code> has been deleted!","center");
			}
		}
		else if($ACTION=="Detect-Paper"){
			$paper=explode("\n",exe("paperconf -aNhwm",true));
			unset($paper[count($paper)-1]);// Delete empty value
			sort($paper);// Lets sort this while we have the chance
			$PAPER=json_decode('{}');
			for($i=0,$s=count($paper);$i<$s;$i++){
				$sheet=explode(" ",$paper[$i]);
				if($sheet[3]<$sheet[1]){
					$tmp=$sheet[3];
					$sheet[3]=$sheet[1];
					$sheet[1]=$tmp;
				}
				$PAPER->{$sheet[0]}=array("height" => $sheet[3], "width" => $sheet[1]);
			}

			if(SaveFile("config/paper.json",json_encode($PAPER))){
				Print_Message("Paper:","$s different paper sizes were detected and are now usable.<br/>The number varies from scanner to scanner",'center');
			}
			else{
				Print_Message("Paper:","$s different paper sizes were detected.<br/>However, <code>$user</code> does not have permission to write files to the <code>".html(getcwd()).'/config</code> folder.','center');
			}
		}
		else if($ACTION=="Delete-Paper"){
			if(@unlink("config/paper.json"))
				Print_Message("Paper:","Paper configuration has been deleted","center");
			else
				Print_Message("Paper:","Failed to delete paper configuration","center");
		}

		if(file_exists("config/settings.json"))
			$file=json_decode(file_get_contents("config/settings.json"));
		else
			$file=json_decode('[]');
		include "res/inc/config.php";

		Footer('');

		if($ACTION=="Search-For-Printers"&&$Printer>0){ # Find avalible printers on the system
			unset($file);
			include('res/printer.php');
			$json=(object)array();
			$list=array();
			for($x=count($printers)-1;$x>-1;$x=$x-1){
				$opt=array_filter(explode("\n",exe('lpoptions -d '.escapeshellarg($printers[$x]).' -l',true)));
				array_push($list,$printers[$x]);
				$arr=array();
				for($i=count($opt)-1;$i>-1;$i=$i-1){
					$line=explode(": ",$opt[$i]);
					$name=explode("/",$line[0]);
					$values=explode(" ",$line[1]);
					for($y=count($values)-1;$y>-1;$y=$y-1){
						if(substr($values[$y],0,1)=='*'){
							$values[$y]=substr($values[$y],1);
							$default=$values[$y];
							break;
						}
					}
					array_push($arr,
						(object)array(
							"name"=>$name[1],
							"id"=>$name[0],
							"value"=>$values,
							"default"=>$default
						)
					);
				}
				$json->{$printers[$x]}=$arr;
			}
			if(count($list)>0){
				$json=(object)array("printers"=>$json,"locations"=>(object)array());
				if(SaveFile("config/printers.json",json_encode($json)))
					Print_Message('Success',count($list).' Printer(s) have been found and configured.<br/><ul style="text-align:left"><li>'.implode("</li><li>",$list).'</li></ul>','center');
				else
					Print_Message('Failure','Bad news: <code>'.$user.'</code> does not have permission to write files to the <code>'.html(getcwd()).'/config</code> folder.','cetner');
			}
			else
				Print_Message('Error','No printers found!!!<br/>Please go to your <a href="http://'.$_SERVER['HTTP_HOST'].':631">CUPS</a> configuration to setup printers.','center');
		}
		else if($ACTION=="Search-For-Scanners"){ # Find avalible scanners on the system
			/*$OP=json_decode( // Double quotes in varables break this
				"[".substr(
					exe('scanimage -f "{\\"ID\\":%i,\\"INUSE\\":0,\\"DEVICE\\":\\"%d\\",\\"NAME\\":\\"%v %m %t\\"},"',true),
					0,
					-1
				)."]"
			);*/
			$OP=array();
			$arr=explode('[=(^^)=]',exe('scanimage -f "%i[=(^^)=]%d[=(^^)=]%v %m %t[=(^^)=]"',true));// If a scanner breaks this it is trying to; Cat in a box: [=(^^)=]
			for($i=0,$max=count($arr);$i<$max-1;$i=$i+3)
				array_push($OP,(object)array("ID"=>intval($arr[$i]),"INUSE"=>0,"DEVICE"=>$arr[$i+1],"NAME"=>$arr[$i+2]));
			$ct=count($OP);
			$scan=scandir('config/parallel');
			for($i=0,$max=count($scan);$i<$max;$i++){
				if($scan[$i]=="."||$scan[$i]=="..")
					continue;
				$OP[$ct]=json_decode(file_get_contents("config/parallel/".$scan[$i]));
				$OP[$ct]->{'ID'}=$ct;
				$OP[$ct]->{'INUSE'}=0;
				$ct++;
			}
			$FakeCt=0;
			if($ExtraScanners){
				$sample=scandir('res/scanhelp');
				unset($sample[0]);unset($sample[1]);// Delete ./ and ../ from the list
				foreach($sample as $key => $val){
					$help=file_get_contents('res/scanhelp/'.$val);
					$help=substr($help,strpos($help,'Options specific to device `')+28);
					$help=substr($help,0,strpos($help,"':"));
					$OP[$ct]=(object)array("ID" => $ct, "INUSE" => 0, "DEVICE" => $help, "NAME" => $val);
					$ct++;
					$FakeCt++;
				}
			}
			for($i=0;$i<$ct;$i++){// Get scanner specific data
				if($i<$ct-$FakeCt){
					if(substr($OP[$i]->{"DEVICE"},0,4)=='net:') // Seems a delay is needed to prevent I/O error (maybe cause the remote server is a raspberry pi; very slow system)
						sleep(1);
					$help=exe("scanimage -A -d ".shell($OP[$i]->{"DEVICE"}),true);
				}
				else
					$help=file_get_contents('res/scanhelp/'.$OP[$i]->{"NAME"});
				// Get Source
				$sources=strpos($help,'--source ');
				if(is_bool($sources))
					$defSource='Inactive';
				else{
					$sources=substr($help,$sources+9);
					$defSource=substr($sources,strpos($sources,' [')+2);
					$ro=$defSource;
					$defSource=substr($defSource,0,strpos($defSource,']'));
					$ro=substr($ro,strlen($defSource)+2,11);
					if($ro=='[read-only]'){
						$defSource='Inactive';
					}
				}
				$OP[$i]->{"SOURCE"}=strtolower($defSource)=='inactive'?'Inactive':substr($sources,0,strpos($sources,' ['));
				$sources=explode('|',$OP[$i]->{"SOURCE"});

				foreach($sources as $key => $val){
					if($val=='Inactive'||$val==$defSource)
						$help2=$help;
					else{
						if($i<$ct-$FakeCt)
							$help2=exe("scanimage -A -d ".shell($OP[$i]->{"DEVICE"})." --source ".shell($val),true);
						else{
							$help2=file_get_contents('res/scanhelp/'.$OP[$i]->{"NAME"});
							exe("echo ".shell("scanimage -A -d 'SIMULATED_$i-$key' --source '$val'"),true);
						}
					}
					if(!is_bool(strpos($help2,' (core dumped)')))
						Print_Message("Warning: scanimage crashed",html($OP[$i]->{"NAME"})." may not be configured properly.<br/>Check the Debug Console for details.",'center');
					if(!is_bool(strpos($help2,' failed: '))){
						$err=substr($help2,strpos($help2,' failed: ')+9);
						$err=substr($err,0,strpos($err,"\n"));
						Print_Message('Failed: '.html($err),html($OP[$i]->{"NAME"})." is probably not configured properly.<br/>Check the Debug Console for details.<br/>".
							"If you got a I/O error and this is a OLD scanner you may need to disable some USB3 XHCI stuff in your BIOS, even if the scanner is on a USB2 port.",'center');
					}
					// Get DPI
					$res=substr($help2,strpos($help2,'--resolution ')+13);
					$res=substr($res,0,strpos($res,'dpi'));
					if(is_int(strpos($res,".."))){// Range of sizes of not it is a list (I want list form)
						$res=explode('..',$res);
						$arr=Array();
						array_push($arr,$res[0]);
						for($x=intval(ceil(($res[0]+1)/100).'00');$x<=$res[1];$x+=100){
							array_push($arr,$x);
						}
						$res=implode("|",$arr);
					}
					else if(is_int(strpos($res,"auto||"))){
						$res='auto'.substr($res,5);
					}
					$OP[$i]->{"DPI-$val"}=$res;
					// Get duplex availability
					$duplex=strpos($help2,'--duplex[=(yes|no)] [');// Looking for this: --duplex[=(yes|no)] [inactive]
					if(!is_bool($duplex)){
						$duplex=substr($help2,$duplex+21);
						$duplex=substr($duplex,0,strpos($duplex,']'));
						$duplex=strtolower($duplex)!=='inactive';
						// TODO: add support for --adf-mode Simplex|Duplex [inactive] (i thought i did this, did i not?)
					}
					else{
						$duplex=strpos($help2,'--adf-mode ');
						if(!is_bool($duplex)){
							$duplex=substr($help2,$duplex+11);
							$duplexOpts=substr($duplex,0,strpos($duplex,' ['));
							$duplex=substr($duplex,strpos($duplex,' [')+2);
							$duplex=substr($duplex,0,strpos($duplex,']'));
							$duplex=strtolower($duplex)!=='inactive'?$duplexOpts:false;
						}
					}
					$OP[$i]->{"DUPLEX-$val"}=$duplex;
					// Get color modes
					$modes=substr($help2,strpos($help2,'--mode ')+7);
					$OP[$i]->{"MODE-$val"}=substr($modes,0,strpos($modes,' ['));
					// Get bay width
					$width=substr($help2,strpos($help2,' -x ')+4);
					$width=substr($width,0,strpos($width,'mm'));
					$OP[$i]->{"WIDTH-$val"}=floatval(substr($width,strpos($width,'..')+2));
					// Get bay height
					$height=substr($help2,strpos($help2,' -y ')+4);
					$height=substr($height,0,strpos($height,'mm'));
					$OP[$i]->{"HEIGHT-$val"}=floatval(substr($height,strpos($height,'..')+2));
					/*if(!is_bool(strpos($OP[$i]->{"DEVICE"},"Deskjet_2050_J510_series"))){// Dirty hack to make scanner work on this model (sane bug?)
						$OP[$i]->{"HEIGHT-$val"}=297.01068878173;# that is as close as php will go without rounding true size is 297.01068878173825282^9
					}*/
					if($val=='Inactive')
						break;
				}

				// Get device vendor ID and product ID (Bug #13)
				$dev=strpos($OP[$i]->{"DEVICE"},"libusb:");
				if(is_bool($dev))
					$OP[$i]->{"UUID"}=NULL;
				else if(substr($OP[$i]->{"DEVICE"},0,4)=='net:'){
					$OP[$i]->{"UUID"}=NULL;
					Print_Message('Warning','You have a networked scanner that uses <code>libusb</code>, the device string for this scanner can change over time.<br/>'.
						'If you connect <code>'.html($OP[$i]->{"NAME"}).'</code> to <code>'.$_SERVER['SERVER_NAME'].'</code> this string can be auto updated so you will not '.
						'have to rescan for scanners after a change.<br/>Things such as reboots and disconnecting the the scanner can change the device string.','center');
				}
				else{
					$dev=substr($OP[$i]->{"DEVICE"},$dev+7,7);
					$dev=exe("lsusb -s ".shell($dev),true);
					$OP[$i]->{"UUID"}=substr($dev,strpos($dev,"ID ")+3,9);
				}
				// Lamp on/off
				//$OP[$i]->{"LAMP"}=!is_bool(strpos($help,'--lamp-switch[=(yes|no)]'))&&!is_bool(strpos($help,'--lamp-off-at-exit[=(yes|no)]'));
			}
			$save=SaveFile("config/scanners.json",json_encode($OP));
			$CANNERS='<table border="1" align="center"><tbody><tr><th>Name</th><th>Device</th></tr>';
			for($i=0;$i<$ct;$i++){
				$CANNERS.='<tr><td>'.html($OP[$i]->{"NAME"}).'</td><td>'.html($OP[$i]->{"DEVICE"}).'</td></tr>';
			}
			$CANNERS.='<tr><td colspan="2" align="center">Missing a scanner? Make sure the scanner is plugged in and turned on.<br/>You may have to use the <a href="index.php?page=Access%20Enabler">Access Enabler</a>.<br/><a href="index.php?page=Parallel-Form">[Click here for parallel-port scanners]</a>'.
				($save?'':'</td></tr><tr><td colspan="2" style="color:red;font-weight:bold;text-align:center;">Bad news: <code>'.$user.'</code> does not have permission to write files to the <code>'.html(getcwd()).'/config</code> folder.<br/><code>sudo chown '.$user.' '.html(getcwd()).'/config</code>').
				'</td></tr>';
			$CANNERS.='</tbod></table>';
			if($ct>1){
				$CANNERS.='<small>It looks like you have more than one scanner. You can change the default scanner on the <a href="index.php?page=Device%20Notes">Scanner List</a> page if you want.</small>';
			}
			if(count($OP)==0)
				Print_Message("No Scanners Found","There were no scanners found on this server. Make sure the scanners are plugged in and turned on. The scanner must also be supported by SANE.<br/>".
					"<a href=\"index.php?page=Parallel-Form\">[Click here for parallel-port scanners]</a><br/>".
					"If it is supported by sane and still does not showup (usb) or does not work (parallel) you may need to use the <a href=\"index.php?page=Access%20Enabler\">Access Enabler</a>".
					(in_array('lp',explode(' ',str_replace("\n",' ',exe("groups ".shell($user),true))))?'':"<br/>It appears $user is not in the lp group! Did you read the <a href=\"index.php?page=About\">Installation Notes</a>?"),'center');
			else
				Print_Message("Scanners Found:",$CANNERS,'center');
		}
	}
}