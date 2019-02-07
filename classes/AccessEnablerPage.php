<?php

class AccessEnablerPage implements Page {

    static function displayPage() {
        InsertHeader("Release Notes");
        include "res/inc/enabler.php";
        Footer('');
    }
}

?>