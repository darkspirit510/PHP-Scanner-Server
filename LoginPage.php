<?php

class LoginPage implements Page {
	
	public static function displayPage() {
		$PAGE='Login';
		InsertHeader('Authenticate Required');
		include('res/inc/login.php');
		Footer('');
	}
}

?>