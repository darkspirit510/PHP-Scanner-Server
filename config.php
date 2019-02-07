<?php

class Config {

    private static $cfgSingleton;
    private $cfg;

    private function __construct() {
        $this->cfg = parse_ini_file('config.ini');
    }

    public function getHomepage() {
        return (string)$this->cfg['HomePage'];
    }

    public static function get() {
        if (Config::$cfgSingleton == null) {
            Config::$cfgSingleton = new Config();
        }

        return Config::$cfgSingleton;
    }
}

// Global Variables are now stored in config.ini
$FreeSpaceWarn = (integer)$cfg['FreeSpaceWarn'];
$Fortune = (bool)$cfg['Fortune'];
$ExtraScanners = (bool)$cfg['ExtraScanners'];
$CheckForUpdates = (bool)$cfg['CheckForUpdates'];
$RequireLogin = (bool)$cfg['RequireLogin'];
$SessionDuration = (integer)$cfg['SessionDuration'];
$Theme = (string)$cfg['Theme'];
$DarkPicker = (bool)$cfg['DarkPicker'];
$RulerIncrement = (double)$cfg['RulerIncrement'];
$TimeZone = (string)$cfg['TimeZone'];
$Printer = (integer)$cfg['Printer'];
$ShowRawFormat = (bool)$cfg['ShowRawFormat'];
$RawScanFormat = (integer)$cfg['RawScanFormat'];
$NAME = (string)$cfg['NAME'];
$VER = (string)$cfg['VER'];
$SAE_VER = (string)$cfg['SAE_VER'];

// Login Stuff
$Auth = true;

if ($RequireLogin) {
    if (!isset($_COOKIE['Authenticated'])) {
        $Auth = false;
    }
    else {
        if (time() > intval($_COOKIE['Authenticated']) + $SessionDuration)// NOT FOR USE ON 32BIT OS IN 2038 http://en.wikipedia.org/wiki/Year_2038_problem
        {
            $Auth = false;
        }
    }
}

spl_autoload_register(function ($class_name) {
    require 'classes/' . $class_name . '.php';
});

?>
