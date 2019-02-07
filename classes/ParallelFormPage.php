<?php

class ParallelFormPage implements Page {

    public static function displayPage() {
        InsertHeader("Parallel Port Scanner Setup");
        $file = fileSafe(Get_Values('file'));
        $name = Get_Values('name');
        $device = Get_Values('device');

        if ($file != null) {
            unlink("config/parallel/$file");
        } else if ($name != null && $device != null) {
            $can = scandir('config/parallel');
            $int = 0;
            while (in_array($int . '.json', $can))
                $int++;
            $save = SaveFile('config/parallel/' . $int . '.json', json_encode(array("NAME" => $name, "DEVICE" => $device)));
        }
        $scan = scandir('config/parallel');
        include "res/inc/parallel.php";
        Footer('');
        if ($name != null && $device != null && $file == null) {
            if (!$save)
                Print_Message("Permissions Error:", "<code>$user</code> does not have permission to write files to <code>" . html(getcwd()) . "/config/parallel</code><br/>" .
                    "<code>sudo chown $user " . html(getcwd()) . "/config/parallel</code>", 'center');
        }
    }
}