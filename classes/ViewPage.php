<?php

class ViewPage implements Page {

    static function displayPage() {
        InsertHeader("View File");
        $file = Get_Values('file');
        if (is_string($file)) {
            $files = json_decode("{\"$file\":1}");
            $prefix = '';
        } else {
            $files = json_decode(Get_Values('json'));
            $prefix = 'Scan_';
        }
        foreach ($files as $file => $val) {
            $file = fileSafe($prefix . $file);
            include "res/inc/view.php";
        }
        echo '<script type="text/javascript">disableIcons();</script>';
        Footer('');
    }
}

?>
