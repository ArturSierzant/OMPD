<?php 
/**
 * In this example we'll get a list of IPs from available servers.
 */

use AdinanCenci\RadioBrowser\RadioBrowser;

//-----------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);

//-----------------------------

require '../vendor/autoload.php';

//-----------------------------

echo '<pre>';
print_r(RadioBrowser::getServerIps());
echo '<pre>';
