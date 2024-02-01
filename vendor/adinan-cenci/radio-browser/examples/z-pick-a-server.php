<?php 
/**
 * Picks a random server
 */

use AdinanCenci\RadioBrowser\RadioBrowser;

//-----------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);

//-----------------------------

require '../vendor/autoload.php';

//-----------------------------

echo '<pre>';
echo RadioBrowser::pickAServer();
echo '</pre>';
