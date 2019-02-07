<?php

class PrinterPage implements Page {
	
	public static function displayPage() {
		if(isset($ACTION)&&$Printer>0){
			InsertHeader('Printer List');
			$json=file_get_contents('config/printers.json');
			
			if(is_bool($json))
				Print_Message("Error",'No printers have been <a href="index.php?page=Printer&action=List">searched for</a>.',"center");
			else{
				echo '<div class="box box-full"><h2>'."$PAGE $ACTION".'</h2><form action="index.php?page=Printer&amp;action=List" method="POST">';
				$json=json_decode(file_get_contents('config/printers.json'));
				$DELETE=Get_Values('delete');
				
				if(isset($DELETE)){
					unset($json->{'printers'}->{$DELETE});
					if(isset($json->{'locations'}->{$DELETE}))
						unset($json->{'locations'}->{$DELETE});
					if(SaveFile('config/printers.json',json_encode($json)))
						Print_Message("Printer has been remove",html($DELETE).' has been removed, It can be reacquired by <a href="index.php?page=Config&action=Search-For-Printers">searching for printers</a>',"center");
					else
						Print_Message("Access Denied","Failed to save changes, ".html($DELETE)." still exist, please contact your administrator. This can not happen unless they wanted it to. Well maybe if something has gone very very wrong.","center");
				}
				else if(count($_POST)>0){
					foreach($_POST as $key => $val){
						if(isset($json->{'printers'}->{$key})&&$val!=''){
							$json->{'locations'}->{$key}=$val;
						}
					}
					if(SaveFile('config/printers.json',json_encode($json)))
						Print_Message("Saved","All printer locations have been stored","center");
					else
						Print_Message("Access Denied","Failed to save changes, ".html($DELETE)." still exist, please contact your administrator. This can not happen unless they wanted it to. Well maybe if something has gone very very wrong.","center");
				}
				
				echo "<ul>";
				
				foreach($json->{"printers"} as $key => $val){
					echo '<li>'.html($key).' <a href="index.php?page=Printer&amp;action=List&amp;delete='.html($key).'" class="del icon tool right"><span class="tip">Remove '.html($key).'</span></a><ul>';
						echo '<li>Location<ul><input name="'.html($key).'" value="'.(isset($json->{"locations"}->{$key})?$json->{"locations"}->{$key}:'').'"/><input type="submit" value="Set"/></ul></li>';
						for($i=count($val)-1;$i>-1;$i=$i-1){
							echo "<li>".$val[$i]->{"name"}.
									"<ul>".implode(", ",$val[$i]->{"value"})."</ul>".
								"</li>";
						}
					echo "</ul></li>";
				}
				
				echo "</ul></form></div>";
			}
		}
		else{
			InsertHeader('Printer');
			include('res/inc/printer.php');
		}
		
		Footer('');
	}
}
?>