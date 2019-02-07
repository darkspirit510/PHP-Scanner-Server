<?php

class PaperManagerPage implements Page {

    static function displayPage() {
        InsertHeader("Paper Manager");
        include "res/inc/paper.php";
        Footer('');
    }
}

?>