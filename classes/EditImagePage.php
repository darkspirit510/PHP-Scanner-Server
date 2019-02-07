<?php

class EditImagePage implements Page {

	public static function displayPage() {
		InsertHeader("Edit Image");
		$file=fileSafe(Get_Values('file'));
		if($file!=null){
			if(substr($file,-3)=="txt")
				include "res/inc/edit-text.php";
			else{
				if(Get_Values('edit')!=null){
					if(file_exists("scans/file/Scan_$file")){
						$langs=findLangs();
						if(!validNum(Array($WIDTH,$HEIGHT,$X_1,$Y_1,$BRIGHT,$CONTRAST,$SCALE,$ROTATE))||
						  ($FILETYPE!=="txt"&&$FILETYPE!=="png"&&$FILETYPE!=="tiff"&&$FILETYPE!=="jpg")||
						  !in_array($LANG,$langs)){
							Print_Message("No, you can not do that","Input data is invalid and most likely an attempt to run malicious code on the server <i>denied</i>",'center');
							Footer('');
							quit();
						}
						$tmpFileRaw="/tmp/Scan_$file";
						$fileRaw="scans/file/Scan_$file";
						if(!@copy($fileRaw,$tmpFileRaw)){
							Print_Message("Permission Error","Unable to create <code>$tmpFileRaw</code>",'center');
							quit();
						}
						$tmpFile=shell($tmpFileRaw);
						$file=shell($fileRaw);
						if($MODE!='color'&&$MODE!=null){
							if($MODE=='gray')
								exe("convert $tmpFile -colorspace Gray $tmpFile",true);
							else if($MODE=='negate')
								exe("convert $tmpFile -negate $tmpFile",true);
							else
								exe("convert $tmpFile -monochrome $tmpFile",true);
						}
						if($BRIGHT!="0"||$CONTRAST!="0"){
							exe("convert $tmpFile -brightness-contrast $BRIGHT".'x'."$CONTRAST $tmpFile",true);
						}
						if($WIDTH!="0"&&$HEIGHT!="0"&&$WIDTH!=null&&$HEIGHT!=null){
							$TRUE=explode("x",exe("identify -format '%wx%h' $file",true));
							$TRUE_W=$TRUE[0];
							$TRUE_H=$TRUE[1];
							$WIDTH=round($WIDTH/$M_WIDTH*$TRUE_W);
							$HEIGHT=round($HEIGHT/$M_HEIGHT*$TRUE_H);
							$X_1=round($X_1/$M_WIDTH*$TRUE_W);
							$Y_1=round($Y_1/$M_HEIGHT*$TRUE_H);
							exe("convert $tmpFile +repage -crop '$WIDTH x $HEIGHT + $X_1 + $Y_1' $tmpFile",true);
						}

						if($SCALE!="100"){
							exe("convert $tmpFile -scale '$SCALE%' $tmpFile",true);
						}
						if($ROTATE!="0"){
							exe("convert $tmpFile -rotate $ROTATE $tmpFile",true);
						}
						exe("convert $tmpFile -alpha off $tmpFile",true);
						$file=substr($fileRaw,16);
						$edit=strpos($file,'-edit-');
						$name=(is_bool($edit)?substr($file,0,-4):substr($file,0,$edit));
						$ext=substr($file,strrpos($file,'.')+1);
						$int=1;
						while(file_exists("scans/thumb/Preview_$name-edit-$int.jpg")){
							$int++;
						}
						$file="scans/file/Scan_$name-edit-$int.$ext";//scan
						$name=str_replace("file/Scan_","thumb/Preview_",$file);//preview
						if($FILETYPE==substr($file,strrpos($file,'.')+1)){
							@rename($tmpFileRaw,$file);// Incorrect access denied message is generated
							if(file_exists($tmpFileRaw)&&!file_exists($file)){// Just in-case it becomes accurate
								copy($tmpFileRaw,$file);
								unlink($tmpFileRaw);
							}
						}
						else if($FILETYPE!='txt'){
							$file=substr($file,0,strrpos($file,'.')+1).$FILETYPE;
							exe("convert $tmpFile ".shell($file),true);
						}
						else{
							$t=time();
							$S_FILENAMET=substr($file,0,strrpos($file,'.'));
							exe("convert $tmpFile -fx '(r+g+b)/3' ".shell("/tmp/edit_scan_file$t.tif"),true);
							exe("tesseract ".shell("/tmp/edit_scan_file$t.tif").' '.shell($S_FILENAMET)." -l ".shell($LANG),true);
							unlink("/tmp/edit_scan_file$t.tif");
							if(!file_exists("$S_FILENAMET.txt"))//In case tesseract fails
								SaveFile("$S_FILENAMET.txt","");
						}
						$FILE=substr($name,0,strrpos($name,'.')+1).'jpg';//Preview
						if($FILETYPE!='txt'){
							exe("convert ".shell($file)." -scale '450x471' ".shell($FILE),true);
							$file=substr($file,16);
						}
						else{
							exe("convert $tmpFile -scale '450x471' ".shell($FILE),true);
							unlink($tmpFileRaw);
							$file=substr($file,16,strrpos($file,'.')-10).'txt';
						}
					}
				}
				if(file_exists("scans/file/Scan_$file")){
					if(substr($file,-3)=="txt")
						include "res/inc/edit-text.php";
					else
						include "res/inc/edit.php";
				}
				else{
					Print_Message("404 Not Found","It appears that <code>$file</code> has been deleted.",'center');
				}
			}
		}
		else{
			if(count(scandir("scans/file"))==2){
				Print_Message("No Images","All files have been removed. There are no scanned images to display.",'center');
			}
			else{
				Print_Message("No File Specified","Please select a file to edit",'center');
				$FILES=sacndir('scans/thumb');
				for($i=0,$max=count($FILES);$i<$max;$i++){
					if($FILES[$i]=='.'||$FILES[$i]=='..')
						continue;
					$FILE=substr($FILES[$i],7,-3);
					$FILE=substr(exe("cd 'scans/file/'; ls ".shell("Scan$FILE").'*',true),5);//Should only have one file listed
					$IMAGE=$FILES[$i];
					include "res/inc/editscans.php";
				}
			}
		}
		checkFreeSpace($FreeSpaceWarn);
		Footer('');
	}
}

?>