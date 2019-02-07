<?php

require 'config.php';
require 'functions.php';

Installation::verify();

switch (Request::getPage()) {
    case 'Printer':
        PrinterPage::displayPage();
        break;
    case 'Scans':
        ScansPage::displayPage();
        break;
    case 'Config':
        ConfigPage::displayPage();
        break;
    case 'Parallel-Form':
        ParallelFormPage::displayPage();
        break;
    case 'About':
        AboutPage::displayPage();
        break;
    case 'PHP Information':
        PhpInformationPage::displayPage();
        break;
    case 'Paper Manager':
        PaperManagerPage::displayPage();
        break;
    case 'Access Enabler':
        AccessEnablerPage::displayPage();
        break;
    case 'Device Notes':
        DeviceNotesPage::displayPage();
        break;
    case 'View':
        ViewPage::displayPage();
        break;
    case 'Edit':
        EditImagePage::displayPage();
        break;
    default:
        ScannerPage::displayPage();
        break;
}

quit();
?>
