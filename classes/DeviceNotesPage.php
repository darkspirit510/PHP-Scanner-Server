<?php

class DeviceNotesPage implements Page {

	public static function displayPage() {
		$id=Get_Values('id');
		if($id!==null){// Set default scanner
			$id=intval($id);
			if(is_int($id)&&file_exists("config/scanners.json")){
				$CANNERS=json_decode(file_get_contents('config/scanners.json'));
				$s=count($CANNERS);
				if($s>$id){
					for($i=0;$i<$s;$i++){
						if(isset($CANNERS[$i]->{"SELECTED"}))
							unset($CANNERS[$i]->{"SELECTED"});
					}
					$CANNERS[$id]->{"SELECTED"}=1;
					SaveFile("config/scanners.json",json_encode($CANNERS));
				}
			}
		}
		if(isset($ACTION)){// Scanner Help
			InsertHeader("Device Info");
			// Bug #13 START
			$CANNERS=json_decode(file_get_contents('config/scanners.json'));
			foreach($CANNERS as $key){
				if($key->{"DEVICE"}==$ACTION){
					if(!is_null($key->{"UUID"})){
						$ACTION=uuid2bus($key);
					}
					break;
				}
			}
			// Bug #13 END
			$SOURCE=Get_Values('source');
			if(is_null($SOURCE))
				$SOURCE='';
			else
				$SOURCE=' --source '.shell($SOURCE);
			$help=exe("scanimage -A -d ".shell($ACTION).$SOURCE,true);
			echo "<div class=\"box box-full\"><h2>$ACTION</h2><pre>".$help."</pre></div>";
		}
		else{// List Scanners
			InsertHeader("Device List");
			if(!isset($CANNERS)){
				if(file_exists("config/scanners.json"))
					$CANNERS=json_decode(file_get_contents("config/scanners.json"));
				else
					$CANNERS=json_decode('[]');
			}
			else{
				Print_Message("New Default Scanner:",$CANNERS[$id]->{"DEVICE"},'center');
			}
			$DELETE=Get_Values('delete');
			if(isset($DELETE)){
				$new=Array();
				$old=$CANNERS[$DELETE]->{"NAME"};
				unset($CANNERS[$DELETE]);
			}
			echo "<div class=\"box box-full\"><h2>Installed Device List</h2>".'<a style="margin-left:5px;" href="index.php?page=Config&amp;action=Search-For-Scanners" onclick="printMsg(\'Searching For Scanners\',\'Please Wait...\',\'center\',0);">Scan for Devices</a>'."<ul>";
			foreach($CANNERS as $i=>$CANNER){
				if(isset($DELETE))
					array_push($new,$CANNER);
				$name=html($CANNER->{"NAME"});
				$DEVICE=html($CANNER->{"DEVICE"});
				$device=url($CANNER->{"DEVICE"});
				$res='';
				$sources=explode('|',$CANNER->{"SOURCE"});
				echo "<li>$name ".(isset($CANNER->{"SELECTED"})?'':"[<a href=\"index.php?page=Device%20Notes&amp;id=$i\">Set as default scanner</a>]").
					"<a href=\"index.php?page=Device%20Notes&amp;delete=$i\" class=\"del icon tool right\"><span class=\"tip\">Remove $name</span></a>".
					"<ul><li><a onclick=\"printMsg('Loading','Please Wait...','center',0);\" href=\"index.php?page=Device%20Notes&amp;action=$device\"><code>$DEVICE</code></a></li>";
				for($x=0,$ct=count($sources);$x<$ct;$x++){
					$val=html($sources[$x]);
					$WIDTH=round($CANNER->{"WIDTH-$val"}/25.4,2);
					$HEIGHT=round($CANNER->{"HEIGHT-$val"}/25.4,2);
					$MODES=count(explode('|',$CANNER->{"MODE-$val"}));
					if($CANNER->{"DPI-$val"}==="")
						$CANNER->{"DPI-$val"}="0|0";
					$DPI=explode('|',$CANNER->{"DPI-$val"});
					echo ($val=='Inactive'?'<li>This scanner supports<ul>':"<li>The '<a onclick=\"printMsg('Loading','Please Wait...','center',0);\" href=\"index.php?page=Device%20Notes&amp;action=$device&amp;source=$val\">$val</a>' source supports<ul>").
						"<li>A bay width of <span class=\"tool\">$WIDTH\"<span class=\"tip\">".$CANNER->{"WIDTH-$val"}." mm</span></span></li>".
						"<li>A bay height of <span class=\"tool\">$HEIGHT\"<span class=\"tip\">".$CANNER->{"HEIGHT-$val"}." mm</span></span></li>".
						'<li>A scanner resolution of '.$DPI[$DPI[0]=='auto'?1:0].' DPI to '.number_format($DPI[count($DPI)-1]).' DPI</li>'.
						'<li>'.($CANNER->{"DUPLEX-$val"}?'D':'No d').'uplex (double sided) scanning</li>'.
						"<li>$MODES color mode".($MODES==1?'':'s')."</li>".
						'</ul></li>';
				}
				echo '</ul></li>';
			}
			echo '</ul></div>';
			if(isset($DELETE)){
				if(SaveFile('config/scanners.json',json_encode($new)))
					Print_Message("Scanner has been remove",html($old).' has been removed, It can be reacquired by <a href="index.php?page=Config&action=Search-For-Scanners">searching for scanners</a>',"center");
				else
					Print_Message("Access Denied","Failed to save changes, ".html($old)." still exist, please contact your administrator. This can not happen unless they wanted it to. Well maybe if something has gone very very wrong.","center");
			}
		}
		Footer('');
	}
}

?>