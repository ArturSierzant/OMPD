<?php 
/**
 * In this example we'll see how to fetch the highest voted stations.
 */

use AdinanCenci\RadioBrowser\RadioBrowser;

//-----------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);

//-----------------------------

require '../vendor/autoload.php';

//-----------------------------

$browser    = new RadioBrowser();
$offset     = 0;    // optional
$limit      = 10;   // optional
$hideBroken = true; // optional
$data       = $browser->getStationsByVotes($offset, $limit, $hideBroken);

echo '<pre>';
print_r($data);
echo '</pre>';
