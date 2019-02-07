<?php

class Installation {

    public static function verify() {
        if (!function_exists("json_decode")) {
            $PAGE = "Incomplete Installation";
            InsertHeader($PAGE);
            Print_Message("Missing Dependency", "<i>php5-json</i> does not appear to be installed, or you forgot to restart <i>apache2</i> after installing it.<br/>Unless it is disabled in <code>php.ini</code>", "center");
            Footer('');
            quit();
        }

        $dirs = Array('config', 'config/parallel', 'scans', 'scans/thumb', 'scans/file');

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir);
                if (is_dir($dir))
                    continue;
                $here = getcwd();
                $PAGE = "Incomplete Installation";
                InsertHeader($PAGE);
                Print_Message("Missing Directory", "<i>$here/$dir</i> does not exist!<br/><code>$user</code> also needs to have write access to it<br>To fix run this in a terminal as root<br><code>mkdir $here/$dir && chown $user $here/$dir</code>", "center");
                Footer('');
                quit();
            }
        }

        if (!function_exists('shell_exec')) {
            $PAGE = "Incomplete Installation";
            InsertHeader($PAGE);
            Print_Message("PHP Configuration Error", "<code>shell_exec</code> is disabled in <code>php.ini</code><br/>It needs to be removed from the <code>disable_functions</code> list.", "center");
            Footer('');
            quit();
        }

        $files = scandir('scans');// Migrate files to new storage layout

        if (count($files) > 5) {
            exe('mv scans/Scan_* scans/file/', true);
            exe('mv scans/Preview_* scans/thumb/', true);
        }
    }
}