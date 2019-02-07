<?php

class ScansPage implements Page {
	
	public static function displayPage() {
		InsertHeader("Scanned Images");

		# Delete selected scanned image
		$DELETE=Get_Values('delete');

		if($DELETE=="Remove"){
			$FILE=fileSafe(Get_Values('file'));
			if($FILE==null){// rm -r scans/*/*
				$files=scandir('scans');
				foreach($files as $file){
					if($file=='.'||$file=='..')
						continue;
					if(is_dir($file)){
						$sub=scandir("scans/$file");
						foreach($sub as $f){
							if($f=='.'||$f=='..')
								continue;
							unlink("scans/$file/$f");
						}
					}
				}
			}
			else{
				$FILE2=substr($FILE,0,strrpos($FILE,".")+1);
				@unlink("scans/thumb/Preview_".$FILE2."jpg");
				@unlink("scans/file/Scan_$FILE");
				Print_Message("File Deleted","The file <code>".html($FILE)."</code> has been removed.",'center');
			}
		}
		
		include('res/inc/scans.php');
		checkFreeSpace($FreeSpaceWarn);
		Footer('');
	}
}