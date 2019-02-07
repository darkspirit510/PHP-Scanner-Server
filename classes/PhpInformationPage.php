<?php

class PhpInformationPage implements Page {
	
	public static function displayPage() {
		InsertHeader($PAGE);
		echo '<div class="box box-full"><h2>'.$PAGE.'</h2><iframe id="phpinfo" src="res/phpinfo.php" style="display:block;border:none;width:100%;height:500px;"></iframe><script type="text/javascript">';
		include "res/writeScripts/phpinfo.js";
		echo '</script></div>';
		Footer('');
	}
}

?>