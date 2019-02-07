<?php

class AboutPage implements Page {
	
	public static function AboutPage() {
		InsertHeader("Release Notes");
		include "res/inc/about.php";
		Footer('');
	}
}